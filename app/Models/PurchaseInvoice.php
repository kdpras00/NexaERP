<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\AutoGeneratesNumber;

use App\Services\AccountingService;

class PurchaseInvoice extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, AutoGeneratesNumber, \App\Traits\BelongsToBranch;

    protected static function booted()
    {
        static::saving(function ($invoice) {
            $invoice->tax_amount = ($invoice->subtotal * $invoice->tax_rate) / 100;
            $invoice->total = $invoice->subtotal + $invoice->tax_amount;
        });

        static::saved(function ($invoice) {
            // 1. Ensure Accounts Payable exists
            $apData = [
                'supplier_id' => $invoice->supplier_id,
                'amount' => $invoice->total,
                'due_date' => $invoice->due_date,
                'status' => $invoice->payment_status === 'paid' ? 'paid' : 'unpaid',
            ];
            
            if (!$invoice->accountsPayable) {
                $invoice->accountsPayable()->create($apData);
            } else {
                $invoice->accountsPayable->update($apData);
            }

            // 2. Automate Stock Movement
            $reference = "Purchase Invoice #{$invoice->number}";
            $existingMovement = StockMovement::where('reference', $reference)->exists();

            if (!$existingMovement) {
                foreach ($invoice->items as $item) {
                    \App\Services\InventoryService::recordMovement(
                        $item->product_id,
                        $item->quantity,
                        'in',
                        $reference,
                        null,
                        $item->unit_price
                    );
                }
            }

            // 3. Create Journal Entry
            $existingJe = JournalEntry::where('description', $reference)->exists();

            if (!$existingJe && $invoice->total > 0) {
                $inventoryAcc = AccountingService::getAccountByCode('1-3100');
                $apAcc = AccountingService::getAccountByCode('2-1100');
                $taxInAcc = AccountingService::getAccountByCode('1-4100'); // PPN Masukan (VAT In)

                if ($inventoryAcc && $apAcc) {
                    $lines = [
                        ['account_id' => $inventoryAcc->id, 'debit' => $invoice->subtotal, 'credit' => 0],
                        ['account_id' => $apAcc->id, 'debit' => 0, 'credit' => $invoice->total],
                    ];

                    if ($invoice->tax_amount > 0 && $taxInAcc) {
                        $lines[] = ['account_id' => $taxInAcc->id, 'debit' => $invoice->tax_amount, 'credit' => 0];
                    }

                    AccountingService::createJournalEntry($invoice->date, $reference, $lines);
                }
            }
        });
    }

    protected $fillable = [
        'number', 'supplier_id', 'branch_id', 'purchase_order_id', 'date', 'due_date',
        'subtotal', 'tax_rate', 'tax_amount', 'total', 'payment_status', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'total' => 'decimal:2',
    ];

    protected static function getNumberPrefix(): string
    {
        return 'PI';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['number', 'payment_status', 'total'])
            ->logOnlyDirty();
    }


    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function accountsPayable()
    {
        return $this->hasOne(AccountsPayable::class);
    }

    public function recalculateTotal(): void
    {
        $this->update([
            'total' => $this->items()->sum('total'),
        ]);
    }
}

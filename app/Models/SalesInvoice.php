<?php

namespace App\Models;

use App\Traits\AutoGeneratesNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

use App\Services\AccountingService;

class SalesInvoice extends Model
{
    use SoftDeletes, LogsActivity, AutoGeneratesNumber, \App\Traits\BelongsToBranch;

    protected static function booted()
    {
        static::saving(function ($invoice) {
            $invoice->tax_amount = ($invoice->subtotal * $invoice->tax_rate) / 100;
            $invoice->total = $invoice->subtotal + $invoice->tax_amount;
        });

        static::saved(function ($invoice) {
            // 1. Ensure Accounts Receivable exists
            $arData = [
                'customer_id' => $invoice->customer_id,
                'amount' => $invoice->total,
                'due_date' => $invoice->due_date,
                'status' => $invoice->payment_status === 'paid' ? 'paid' : 'unpaid',
            ];

            if (!$invoice->accountsReceivable) {
                $invoice->accountsReceivable()->create($arData);
            } else {
                $invoice->accountsReceivable->update($arData);
            }

            // 2. Automate Stock Movement & Journal Entries
            $reference = "Sales Invoice #{$invoice->number}";
            $existingMovement = StockMovement::where('reference', $reference)->exists();

            if (!$existingMovement) {
                foreach ($invoice->items as $item) {
                    \App\Services\InventoryService::recordMovement(
                        $item->product_id,
                        $item->quantity,
                        'out',
                        $reference,
                        null,
                        $item->unit_price // Recording sales price in movement reference or actual cost is handled by service
                    );
                }
            }

            // 3. Create Journal Entry
            $existingJe = JournalEntry::where('description', $reference)->exists();

            if (!$existingJe && $invoice->total > 0) {
                $arAcc = AccountingService::getAccountByCode('1-2100');
                $salesAcc = AccountingService::getAccountByCode('4-1100');
                $taxOutAcc = AccountingService::getAccountByCode('2-1200'); // PPN Keluaran
                $inventoryAcc = AccountingService::getAccountByCode('1-3100');
                $cogsAcc = AccountingService::getAccountByCode('5-1100');

                if ($arAcc && $salesAcc) {
                    $lines = [
                        ['account_id' => $arAcc->id, 'debit' => $invoice->total, 'credit' => 0],
                        ['account_id' => $salesAcc->id, 'debit' => 0, 'credit' => $invoice->subtotal],
                    ];

                    if ($invoice->tax_amount > 0 && $taxOutAcc) {
                        $lines[] = ['account_id' => $taxOutAcc->id, 'debit' => 0, 'credit' => $invoice->tax_amount];
                    }

                    // COGS Calculation
                    $totalCOGS = 0;
                    foreach ($invoice->items as $item) {
                        $totalCOGS += \App\Services\InventoryService::calculateCOGS($item->product_id, $item->quantity);
                    }

                    if ($totalCOGS > 0 && $inventoryAcc && $cogsAcc) {
                        $lines[] = ['account_id' => $cogsAcc->id, 'debit' => $totalCOGS, 'credit' => 0];
                        $lines[] = ['account_id' => $inventoryAcc->id, 'debit' => 0, 'credit' => $totalCOGS];
                    }

                    AccountingService::createJournalEntry($invoice->date, $reference, $lines);
                }
            }
        });
    }

    protected $fillable = [
        'number', 'customer_id', 'branch_id', 'sales_order_id', 'date', 'due_date',
        'subtotal', 'tax_rate', 'tax_amount', 'total', 'payment_status', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'total' => 'decimal:2',
    ];

    protected static function getNumberPrefix(): string
    {
        return 'SI';
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items()
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function accountsReceivable()
    {
        return $this->hasOne(AccountsReceivable::class);
    }

    public function recalculateTotal(): void
    {
        $this->update([
            'total' => $this->items()->sum('total'),
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['number', 'payment_status', 'total'])
            ->logOnlyDirty();
    }
}

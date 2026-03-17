<?php

namespace App\Models;

use App\Traits\AutoGeneratesNumber;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseOrder extends Model
{
    use SoftDeletes, LogsActivity, AutoGeneratesNumber, \App\Traits\BelongsToBranch;

    protected $fillable = [
        'number', 'supplier_id', 'branch_id', 'purchase_request_id', 'date', 
        'subtotal', 'tax_rate', 'tax_amount', 'total', 'status', 'notes',
        'approved_by', 'project_id'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    protected $casts = [
        'date' => 'date',
        'total' => 'decimal:2',
    ];

    protected static function getNumberPrefix(): string
    {
        return 'PO';
    }

    protected static function booted()
    {
        static::saving(function ($order) {
            $order->tax_amount = ($order->subtotal * $order->tax_rate) / 100;
            $order->total = $order->subtotal + $order->tax_amount;
        });

        static::updated(function ($order) {
            if ($order->wasChanged('status')) {
                if ($order->status === 'pending_approval') {
                    $admins = User::role('Admin')->get();
                    foreach ($admins as $admin) {
                        Notification::make()
                            ->title('PO Pending Approval')
                            ->info()
                            ->body("Purchase Order #{$order->number} from {$order->supplier->name} requires approval.")
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->button()
                                    ->url(\App\Filament\Resources\PurchaseOrderResource::getUrl('edit', ['record' => $order])),
                            ])
                            ->sendToDatabase($admin);
                    }
                }
            }
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function purchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class);
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
            ->logOnly(['number', 'status', 'total'])
            ->logOnlyDirty();
    }
}

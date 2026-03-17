<?php

namespace App\Models;

use App\Traits\AutoGeneratesNumber;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SalesOrder extends Model
{
    use SoftDeletes, LogsActivity, AutoGeneratesNumber, \App\Traits\BelongsToBranch;

    protected $fillable = [
        'number', 'customer_id', 'branch_id', 'quotation_id', 'date', 
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
        return 'SO';
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
                            ->title('SO Pending Approval')
                            ->info()
                            ->body("Sales Order #{$order->number} from {$order->customer->name} requires approval.")
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->button()
                                    ->url(\App\Filament\Resources\SalesOrderResource::getUrl('edit', ['record' => $order])),
                            ])
                            ->sendToDatabase($admin);
                    }
                }
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class);
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

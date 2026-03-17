<?php

namespace App\Models;

use App\Traits\AutoGeneratesNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Quotation extends Model
{
    use SoftDeletes, LogsActivity, AutoGeneratesNumber, \App\Traits\BelongsToBranch;

    protected $fillable = [
        'number', 'customer_id', 'branch_id', 'date', 'status', 'total_amount',
        'valid_until', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'valid_until' => 'date',
        'total_amount' => 'decimal:2',
    ];

    protected static function getNumberPrefix(): string
    {
        return 'QUO';
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function recalculateTotal(): void
    {
        $this->update([
            'total_amount' => $this->items()->sum('total'),
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['number', 'status', 'total_amount'])
            ->logOnlyDirty();
    }
}

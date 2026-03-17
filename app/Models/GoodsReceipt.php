<?php

namespace App\Models;

use App\Traits\AutoGeneratesNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class GoodsReceipt extends Model
{
    use SoftDeletes, LogsActivity, AutoGeneratesNumber, \App\Traits\BelongsToBranch;

    protected $fillable = ['number', 'purchase_order_id', 'branch_id', 'warehouse_id', 'date', 'status', 'notes'];
    protected $casts = ['date' => 'date'];

    protected static function getNumberPrefix(): string
    {
        return 'GR';
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['number', 'status'])
            ->logOnlyDirty();
    }
}

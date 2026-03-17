<?php

namespace App\Models;

use App\Traits\AutoGeneratesNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use SoftDeletes, AutoGeneratesNumber, \App\Traits\BelongsToBranch;

    protected $fillable = [
        'number', 'from_warehouse_id', 'to_warehouse_id', 'date', 'status', 'notes', 'branch_id'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function getNumberPrefix(): string
    {
        return 'TRF';
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $fillable = ['warehouse_id', 'product_id', 'branch_id', 'system_stock', 'physical_stock', 'difference', 'notes'];

    protected static function booted()
    {
        static::created(function ($opname) {
            $product = $opname->product;
            $diff = $opname->difference;
            
            if ($diff > 0) {
                $product->increment('stock_quantity', $diff);
                $type = 'in';
            } elseif ($diff < 0) {
                $product->decrement('stock_quantity', abs($diff));
                $type = 'out';
            } else {
                return;
            }

            \App\Models\StockMovement::create([
                'product_id' => $opname->product_id,
                'warehouse_id' => $opname->warehouse_id,
                'quantity' => abs($diff),
                'type' => $type,
                'reference' => "Stock Opname: " . ($opname->notes ?: 'Regular check'),
                'date' => now(),
            ]);
        });
    }

    public function product() { return $this->belongsTo(Product::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
}

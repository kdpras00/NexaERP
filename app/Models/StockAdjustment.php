<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $fillable = ['product_id', 'warehouse_id', 'branch_id', 'adjustment_type', 'quantity', 'reason'];

    protected static function booted()
    {
        static::created(function ($adjustment) {
            $product = $adjustment->product;
            $quantity = $adjustment->quantity;
            
            if ($adjustment->adjustment_type === 'increase') {
                $product->increment('stock_quantity', $quantity);
                $type = 'in';
            } else {
                $product->decrement('stock_quantity', $quantity);
                $type = 'out';
            }

            \App\Models\StockMovement::create([
                'product_id' => $adjustment->product_id,
                'warehouse_id' => $adjustment->warehouse_id,
                'quantity' => $quantity,
                'type' => $type,
                'reference' => "Adjustment: " . ($adjustment->reason ?: 'No reason'),
                'date' => now(),
            ]);
        });
    }

    public function product() { return $this->belongsTo(Product::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
}

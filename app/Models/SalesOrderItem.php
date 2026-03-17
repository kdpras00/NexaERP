<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    protected $fillable = ['sales_order_id', 'product_id', 'quantity', 'price', 'total'];
    protected $casts = ['price' => 'decimal:2', 'total' => 'decimal:2'];

    public function salesOrder() { return $this->belongsTo(SalesOrder::class); }
    public function product() { return $this->belongsTo(Product::class); }
}

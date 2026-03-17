<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceItem extends Model
{
    protected $fillable = ['sales_invoice_id', 'product_id', 'quantity', 'price', 'total'];
    protected $casts = ['price' => 'decimal:2', 'total' => 'decimal:2'];

    public function salesInvoice() { return $this->belongsTo(SalesInvoice::class); }
    public function product() { return $this->belongsTo(Product::class); }
}

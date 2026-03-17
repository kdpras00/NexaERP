<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountsPayable extends Model
{
    protected $fillable = ['supplier_id', 'purchase_invoice_id', 'amount', 'due_date', 'status'];
    protected $casts = ['due_date' => 'date', 'amount' => 'decimal:2'];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function purchaseInvoice() { return $this->belongsTo(PurchaseInvoice::class); }
}

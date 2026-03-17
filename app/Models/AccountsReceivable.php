<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountsReceivable extends Model
{
    protected $fillable = ['customer_id', 'sales_invoice_id', 'amount', 'due_date', 'status'];
    protected $casts = ['due_date' => 'date', 'amount' => 'decimal:2'];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function salesInvoice() { return $this->belongsTo(SalesInvoice::class); }
}

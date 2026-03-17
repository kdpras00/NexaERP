<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'email', 'phone', 'address', 'tax_number'];

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function accountsReceivables()
    {
        return $this->hasMany(AccountsReceivable::class);
    }
}

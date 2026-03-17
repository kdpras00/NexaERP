<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $fillable = ['name', 'description', 'gl_account_id'];

    public function glAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'gl_account_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}

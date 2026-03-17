<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $fillable = ['account_id', 'branch_id', 'amount', 'period_month', 'period_year'];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }
}

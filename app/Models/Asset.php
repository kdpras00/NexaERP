<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes, \App\Traits\BelongsToBranch;

    protected $fillable = [
        'name', 'code', 'purchase_date', 'value', 'residual_value', 
        'useful_life_months', 'depreciation_method', 'last_depreciation_date', 
        'location', 'branch_id', 'account_id', 'status'
    ];
    
    protected $casts = [
        'purchase_date' => 'date', 
        'last_depreciation_date' => 'date',
        'value' => 'decimal:2',
        'residual_value' => 'decimal:2',
    ];

    public function account() { return $this->belongsTo(ChartOfAccount::class); }

    public function maintenances() { return $this->hasMany(AssetMaintenance::class); }
}

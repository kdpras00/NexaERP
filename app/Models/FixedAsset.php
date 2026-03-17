<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    protected $fillable = [
        'asset_code', 'name', 'category', 'purchase_date', 
        'purchase_cost', 'salvage_value', 'useful_life_years', 
        'accumulated_depreciation', 'status', 'branch_id'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getBookValueAttribute()
    {
        return $this->purchase_cost - $this->accumulated_depreciation;
    }
}

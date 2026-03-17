<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetMaintenance extends Model
{
    protected $fillable = ['asset_id', 'maintenance_date', 'description', 'cost'];
    protected $casts = ['maintenance_date' => 'date', 'cost' => 'decimal:2'];

    public function asset() { return $this->belongsTo(Asset::class); }
}

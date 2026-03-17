<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualityControl extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'production_order_id', 'inspector_id', 'status',
        'notes', 'passed_quantity', 'failed_quantity', 'checked_at'
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}

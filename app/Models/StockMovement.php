<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $fillable = ['product_id', 'warehouse_id', 'branch_id', 'project_id', 'quantity', 'type', 'reference', 'date', 'unit_cost'];

    protected $casts = ['date' => 'date'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}

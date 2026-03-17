<?php

namespace App\Models;

use App\Traits\AutoGeneratesNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillOfMaterial extends Model
{
    use SoftDeletes, AutoGeneratesNumber;

    protected $fillable = ['number', 'product_id', 'quantity', 'is_active', 'instructions'];

    protected static function getNumberPrefix(): string
    {
        return 'BOM';
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(BomItem::class);
    }
}

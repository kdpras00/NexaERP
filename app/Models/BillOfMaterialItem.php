<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillOfMaterialItem extends Model
{
    protected $table = 'bom_items';
    protected $fillable = ['bill_of_material_id', 'product_id', 'quantity'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

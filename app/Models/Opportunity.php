<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $fillable = [
        'lead_id', 'branch_id', 'title', 'value', 'stage', 
        'expected_closed_date', 'probability'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expected_closed_date' => 'date',
        'probability' => 'integer',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}

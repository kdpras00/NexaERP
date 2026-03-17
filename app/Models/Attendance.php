<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $fillable = [
        'employee_id', 'branch_id', 'date', 'check_in', 'check_out', 
        'status', 'location', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    public function employee() 
    { 
        return $this->belongsTo(Employee::class); 
    }
}

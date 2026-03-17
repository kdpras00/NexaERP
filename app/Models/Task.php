<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = ['project_id', 'name', 'assigned_to', 'start_date', 'due_date', 'status'];
    protected $casts = ['start_date' => 'date', 'due_date' => 'date'];

    public function project() { return $this->belongsTo(Project::class); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
}

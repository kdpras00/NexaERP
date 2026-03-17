<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'client', 'start_date', 'end_date', 'budget', 'status'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'budget' => 'decimal:2'];

    public function tasks() { return $this->hasMany(Task::class); }
    public function expenses() { return $this->hasMany(Expense::class); }
}

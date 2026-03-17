<?php

namespace App\Models;

use App\Traits\AutoGeneratesNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class JournalEntry extends Model
{
    use SoftDeletes, LogsActivity, AutoGeneratesNumber, \App\Traits\BelongsToBranch;

    protected $fillable = ['number', 'date', 'description', 'status', 'created_by', 'branch_id', 'project_id'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    protected $casts = ['date' => 'date'];

    protected static function getNumberPrefix(): string
    {
        return 'JE';
    }

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->lines()->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->lines()->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['number', 'status', 'description'])
            ->logOnlyDirty();
    }
}

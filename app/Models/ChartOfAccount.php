<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'type', 'parent_id'];

    public function parent() { return $this->belongsTo(ChartOfAccount::class, 'parent_id'); }
    public function children() { return $this->hasMany(ChartOfAccount::class, 'parent_id'); }

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Company extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = ['name', 'logo', 'address', 'phone', 'email', 'tax_number'];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'email'])->logOnlyDirty();
    }
}

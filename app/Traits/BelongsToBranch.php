<?php

namespace App\Traits;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToBranch
{
    public static function bootBelongsToBranch()
    {
        static::addGlobalScope(new class implements \Illuminate\Database\Eloquent\Scope {
            public function apply(Builder $builder, \Illuminate\Database\Eloquent\Model $model) {
                if (auth()->check() && !auth()->user()->hasRole('Admin') && auth()->user()->branch_id) {
                    $builder->where($model->getTable() . '.branch_id', auth()->user()->branch_id);
                }
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && !$model->branch_id) {
                $model->branch_id = auth()->user()->branch_id;
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait AutoGeneratesNumber
{
    public static function bootAutoGeneratesNumber(): void
    {
        static::creating(function ($model) {
            if (empty($model->number)) {
                $model->number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $prefix = static::getNumberPrefix();
        $date = now()->format('Ym');
        $pattern = "{$prefix}-{$date}-";

        $lastNumber = DB::table((new static)->getTable())
            ->where('number', 'like', "{$pattern}%")
            ->orderByDesc('number')
            ->value('number');

        if ($lastNumber) {
            $lastSeq = (int) substr($lastNumber, -4);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $pattern . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    abstract protected static function getNumberPrefix(): string;
}

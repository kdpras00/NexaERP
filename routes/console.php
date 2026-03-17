<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\AssetService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Truly Automatic ERP: Run Monthly Asset Depreciation
Schedule::call(function () {
    AssetService::runMonthlyDepreciation();
})->monthlyOn(1, '01:00');

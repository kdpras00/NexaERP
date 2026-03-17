<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$accounts = \App\Models\ChartOfAccount::where('code', 'like', '6-%')
    ->orWhere('name', 'like', '%Depreciation%')
    ->get(['code', 'name']);

foreach ($accounts as $a) {
    echo $a->code . ": " . $a->name . "\n";
}

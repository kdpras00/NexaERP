<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$expenseParent = \App\Models\ChartOfAccount::firstOrCreate(
    ['code' => '6-2000'],
    ['name' => 'Depreciation Expenses', 'type' => 'expense', 'description' => 'Annual/Monthly Depreciation of Assets']
);

\App\Models\ChartOfAccount::firstOrCreate(
    ['code' => '6-2100'],
    ['name' => 'Depreciation Expense - Equipment', 'type' => 'expense', 'parent_id' => $expenseParent->id]
);

$accumParent = \App\Models\ChartOfAccount::firstOrCreate(
    ['code' => '1-3000'],
    ['name' => 'Accumulated Depreciation', 'type' => 'asset', 'description' => 'Contra-asset accounts']
);

\App\Models\ChartOfAccount::firstOrCreate(
    ['code' => '1-3100'],
    ['name' => 'Accumulated Depreciation - Equipment', 'type' => 'asset', 'parent_id' => $accumParent->id]
);

echo "Depreciation accounts created.\n";

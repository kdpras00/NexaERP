<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'assets',
            'attendances',
            'payrolls',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'branch_id')) {
                        $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'assets',
            'attendances',
            'payrolls',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'branch_id')) {
                        $table->dropConstrainedForeignId('branch_id');
                    }
                });
            }
        }
    }
};

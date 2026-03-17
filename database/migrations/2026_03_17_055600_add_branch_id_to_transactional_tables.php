<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'journal_entries',
            'cash_transactions',
            'quotations',
            'sales_orders',
            'sales_invoices',
            'purchase_orders',
            'purchase_invoices',
            'delivery_orders',
            'goods_receipts',
            'purchase_requests',
            'stock_movements',
            'stock_adjustments',
            'stock_opnames',
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
            'journal_entries',
            'cash_transactions',
            'quotations',
            'sales_orders',
            'sales_invoices',
            'purchase_orders',
            'purchase_invoices',
            'delivery_orders',
            'goods_receipts',
            'purchase_requests',
            'stock_movements',
            'stock_adjustments',
            'stock_opnames',
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

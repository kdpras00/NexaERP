<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->decimal('residual_value', 15, 2)->default(0)->after('value');
            $table->integer('useful_life_months')->default(48)->after('residual_value');
            $table->string('depreciation_method')->default('straight_line')->after('useful_life_months');
            $table->date('last_depreciation_date')->nullable()->after('depreciation_method');
            $table->foreignId('branch_id')->nullable()->after('location')->constrained()->nullOnDelete();
            $table->foreignId('account_id')->nullable()->after('branch_id')->constrained('chart_of_accounts')->nullOnDelete();
        });
        
        // Add approved_by to PO and SO for Workflow
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['residual_value', 'useful_life_months', 'depreciation_method', 'last_depreciation_date', 'branch_id', 'account_id']);
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
        });
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
        });
    }
};

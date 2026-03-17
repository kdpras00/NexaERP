<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0)->after('due_date');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('supplier_id')->constrained()->nullOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0)->after('due_date');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
        });
        
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0)->after('date');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('supplier_id')->constrained()->nullOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0)->after('date');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn(['subtotal', 'tax_rate', 'tax_amount']);
        });
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn(['subtotal', 'tax_rate', 'tax_amount']);
        });
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn(['subtotal', 'tax_rate', 'tax_amount']);
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn(['subtotal', 'tax_rate', 'tax_amount']);
        });
    }
};

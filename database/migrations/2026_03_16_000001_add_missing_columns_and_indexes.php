<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Products: add min_stock for low stock alerts
        Schema::table('products', function (Blueprint $table) {
            $table->integer('min_stock')->default(10)->after('stock_quantity');
        });

        // Sales Orders: add quotation_id for document flow
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('quotation_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->text('notes')->nullable()->after('total');
        });

        // Sales Invoices: add sales_order_id for document flow
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->foreignId('sales_order_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->text('notes')->nullable()->after('payment_status');
        });

        // Purchase Invoices: add purchase_order_id for document flow
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')->nullable()->after('supplier_id')->constrained()->nullOnDelete();
            $table->text('notes')->nullable()->after('payment_status');
        });

        // Purchase Orders: add notes and purchase_request_id
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('purchase_request_id')->nullable()->after('supplier_id')->constrained()->nullOnDelete();
            $table->text('notes')->nullable()->after('status');
        });

        // Quotations: add notes, valid_until
        Schema::table('quotations', function (Blueprint $table) {
            $table->date('valid_until')->nullable()->after('total_amount');
            $table->text('notes')->nullable()->after('valid_until');
        });

        // Delivery Orders: add notes
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('status');
        });

        // Goods Receipts: add status, notes
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('date');
            $table->text('notes')->nullable()->after('status');
        });

        // Employees: add branch_id, email, phone
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
        });

        // Stock Adjustments: add date, status
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->date('date')->nullable()->after('reason');
            $table->string('status')->default('pending')->after('date');
        });

        // Stock Opnames: add date, status
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->date('date')->nullable()->after('notes');
            $table->string('status')->default('draft')->after('date');
        });

        // Journal Entries: add status, created_by
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('description');
            $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });

        // Cash Transactions: add created_by, notes
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('account_id')->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable()->after('created_by');
        });

        // Purchase Requests: add notes, total
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        // Add indexes for performance
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('date');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('date');
        });
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->index('payment_status');
            $table->index('date');
        });
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->index('payment_status');
            $table->index('date');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->index('stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['stock_quantity']);
            $table->dropColumn('min_stock');
        });
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['date']);
            $table->dropConstrainedForeignId('quotation_id');
            $table->dropColumn('notes');
        });
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['date']);
            $table->dropConstrainedForeignId('sales_order_id');
            $table->dropColumn('notes');
        });
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['date']);
            $table->dropConstrainedForeignId('purchase_order_id');
            $table->dropColumn('notes');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['date']);
            $table->dropConstrainedForeignId('purchase_request_id');
            $table->dropColumn('notes');
        });
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['valid_until', 'notes']);
        });
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropColumn(['status', 'notes']);
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn(['email', 'phone']);
        });
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropColumn(['date', 'status']);
        });
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropColumn(['date', 'status']);
        });
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropConstrainedForeignId('created_by');
        });
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('notes');
        });
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('notes');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn('approved_at');
        });
    }
};

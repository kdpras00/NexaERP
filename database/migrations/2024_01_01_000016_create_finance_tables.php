<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chart of Accounts
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type'); // asset, liability, equity, revenue, expense
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Journal Entries
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->timestamps();
        });

        // Cash Transactions
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->enum('type', ['in', 'out']);
            $table->string('source_or_purpose');
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('date');
            $table->foreignId('account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Accounts Payable
        Schema::create('accounts_payables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('due_date');
            $table->string('status')->default('unpaid');
            $table->timestamps();
        });

        // Accounts Receivable
        Schema::create('accounts_receivables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('due_date');
            $table->string('status')->default('unpaid');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts_receivables');
        Schema::dropIfExists('accounts_payables');
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
    }
};

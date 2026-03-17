<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->string('reconciliation_status')->default('unreconciled')->after('notes');
            $table->timestamp('matched_at')->nullable()->after('reconciliation_status');
            $table->string('bank_reference')->nullable()->after('matched_at');
        });
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropColumn(['reconciliation_status', 'matched_at', 'bank_reference']);
        });
    }
};

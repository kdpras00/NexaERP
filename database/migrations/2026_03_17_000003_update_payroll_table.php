<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('employee_id')->constrained()->nullOnDelete();
            $table->string('status')->default('draft')->after('payment_date');
            $table->decimal('total_amount', 15, 2)->default(0)->after('deductions');
        });
        
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn(['status', 'total_amount']);
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};

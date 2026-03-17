<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 2)->default(0)->after('quantity');
        });
        
        // Add current cost to products for reference
        Schema::table('products', function (Blueprint $table) {
            $table->string('valuation_method')->default('fifo')->after('cost_price'); // fifo, average
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('valuation_method');
        });
    }
};

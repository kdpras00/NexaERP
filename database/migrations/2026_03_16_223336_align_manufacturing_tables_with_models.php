<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_of_materials', function (Blueprint $table) {
            if (!Schema::hasColumn('bill_of_materials', 'number')) {
                $table->string('number')->unique()->after('id');
            }
            if (!Schema::hasColumn('bill_of_materials', 'quantity')) {
                $table->decimal('quantity', 15, 2)->default(1)->after('product_id');
            }
            if (!Schema::hasColumn('bill_of_materials', 'instructions')) {
                $table->text('instructions')->nullable()->after('is_active');
            }
        });

        Schema::table('production_orders', function (Blueprint $table) {
            if (Schema::hasColumn('production_orders', 'quantity_planned') && !Schema::hasColumn('production_orders', 'quantity_to_produce')) {
                $table->renameColumn('quantity_planned', 'quantity_to_produce');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bill_of_materials', function (Blueprint $table) {
            $table->dropColumn(['number', 'quantity', 'instructions']);
        });

        Schema::table('production_orders', function (Blueprint $table) {
            if (Schema::hasColumn('production_orders', 'quantity_to_produce')) {
                $table->renameColumn('quantity_to_produce', 'quantity_planned');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add product type
        Schema::table('products', function (Blueprint $table) {
            $table->string('type')->default('inventory')->after('unit_id'); // inventory, raw_material, finished_good, service
        });

        // Bills of Materials
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // The finished good
            $table->string('version')->default('1.0');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_of_material_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained(); // The raw material
            $table->decimal('quantity', 15, 4);
            $table->timestamps();
        });

        // Production Orders (Work Orders)
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('product_id')->constrained(); // Finished good to produce
            $table->foreignId('bill_of_material_id')->constrained();
            $table->foreignId('warehouse_id')->constrained(); // Where finished goods go
            $table->foreignId('branch_id')->nullable()->constrained();
            $table->decimal('quantity_planned', 15, 2);
            $table->decimal('quantity_produced', 15, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('draft'); // draft, planned, in_progress, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('bill_of_materials');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};

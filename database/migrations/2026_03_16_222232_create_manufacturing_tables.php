<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bill of Materials (BoM)
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('product_id')->constrained('products'); // Finished Good
            $table->decimal('quantity', 15, 2)->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('instructions')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_of_material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products'); // Raw Material
            $table->decimal('quantity', 15, 2);
            $table->timestamps();
        });

        // Production Orders (Work Orders)
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('bill_of_material_id')->constrained();
            $table->foreignId('branch_id')->constrained();
            $table->decimal('quantity_to_produce', 15, 2);
            $table->decimal('quantity_produced', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('draft'); // draft, confirmed, in_progress, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('bill_of_materials');
    }
};

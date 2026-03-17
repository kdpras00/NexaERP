<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspector_id')->constrained('users');
            $table->string('status'); // pass, fail, needs_rework
            $table->text('notes')->nullable();
            $table->integer('passed_quantity');
            $table->integer('failed_quantity');
            $table->timestamp('checked_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_controls');
    }
};

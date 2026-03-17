<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('company');
            $table->string('contact_person');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->nullable(); // Website, Referral, etc.
            $table->string('status')->default('new'); // new, contacted, qualified, lost
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->decimal('value', 15, 2)->default(0);
            $table->string('stage')->default('prospecting'); // prospecting, qualification, proposal, negotiation, closed_won, closed_lost
            $table->date('expected_closed_date')->nullable();
            $table->integer('probability')->default(10); // 10% to 100%
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('leads');
    }
};

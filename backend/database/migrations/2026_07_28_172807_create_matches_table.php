<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_a_id')->constrained('pets')->cascadeOnDelete();
            $table->foreignId('pet_b_id')->constrained('pets')->cascadeOnDelete();
            $table->string('status', 30)->default('pending');
            $table->timestamp('matched_at')->nullable();
            $table->timestamps();

            $table->unique(['pet_a_id', 'pet_b_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};

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
        Schema::create('pet_swipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('swiper_pet_id')->constrained('pets')->cascadeOnDelete();
            $table->foreignId('swiped_pet_id')->constrained('pets')->cascadeOnDelete();
            $table->string('action', 20);
            $table->timestamps();

            $table->unique(['swiper_pet_id', 'swiped_pet_id']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_swipes');
    }
};

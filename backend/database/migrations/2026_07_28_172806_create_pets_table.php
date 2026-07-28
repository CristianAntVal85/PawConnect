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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('species');
            $table->string('name');
            $table->string('breed')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->string('size', 30)->nullable();
            $table->string('energy_level', 30)->nullable();
            $table->text('personality')->nullable();
            $table->boolean('available_for_walks')->default(true);
            $table->timestamps();

            $table->index(['species', 'available_for_walks']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};

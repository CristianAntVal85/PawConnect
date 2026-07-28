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
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->date('last_date')->nullable();
            $table->unsignedInteger('periodicity_days')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('notifications_active')->default(true);
            $table->date('next_due_date')->nullable();
            $table->timestamps();

            $table->index(['pet_id', 'type']);
            $table->index('next_due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};

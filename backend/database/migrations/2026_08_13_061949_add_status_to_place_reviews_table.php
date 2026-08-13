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
        Schema::table('place_reviews', function (Blueprint $table) {
            $table->enum('status', ['approved', 'pending', 'rejected'])->default('approved')->after('photo');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('place_reviews', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'rejection_reason']);
        });
    }
};

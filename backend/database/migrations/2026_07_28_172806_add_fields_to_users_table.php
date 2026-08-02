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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin'])->default('user')->after('password');
            $table->string('photo')->nullable()->after('role');
            $table->decimal('lat', 10, 7)->nullable()->after('photo');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->string('address')->nullable()->after('lng');
            $table->string('billing_address')->nullable()->after('address');

            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn([
                'role',
                'photo',
                'lat',
                'lng',
                'address',
                'billing_address',
            ]);
        });
    }
};

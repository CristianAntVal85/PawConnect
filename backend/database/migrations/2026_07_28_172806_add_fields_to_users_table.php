<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Esta migración es ahora innecesaria.
     * Los campos (role, photo, lat, lng, address, billing_address)
     * ya están en create_users_table.php
     * Se mantiene para compatibilidad pero no hace nada.
     */
    public function up(): void
    {
        // No-op: los campos ya existen en create_users_table
    }

    public function down(): void
    {
        // No-op
    }
};

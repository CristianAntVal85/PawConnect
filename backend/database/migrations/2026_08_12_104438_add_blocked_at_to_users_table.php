<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Esta migración es ahora innecesaria.
     * El campo `blocked_at` ya está en create_users_table.php
     * Se mantiene para compatibilidad pero no hace nada.
     */
    public function up(): void
    {
        // No-op: el campo ya existe en create_users_table
    }

    public function down(): void
    {
        // No-op
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nombre', 100)->nullable();
            $table->string('apellido', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 200)->nullable();
        });

        // Add enum columns via raw statements to use PostgreSQL native enums
        DB::statement("ALTER TABLE users ADD COLUMN estado estado_usuario DEFAULT 'activo'");
        DB::statement("ALTER TABLE users ADD COLUMN tipo_usuario tipo_usuario DEFAULT 'cliente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop enum columns first
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['estado', 'tipo_usuario']);
        });

        // Drop standard columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'apellido', 'telefono', 'direccion']);
        });
    }
};

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
        // Create PostgreSQL enum types if they don't exist
        if (!DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'estado_usuario'")) {
            DB::statement("CREATE TYPE estado_usuario AS ENUM ('activo', 'inactivo')");
        }
        if (!DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'tipo_usuario'")) {
            DB::statement("CREATE TYPE tipo_usuario AS ENUM ('propietario', 'barbero', 'cliente')");
        }
        if (!DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'estado_barbero'")) {
            DB::statement("CREATE TYPE estado_barbero AS ENUM ('disponible', 'ocupado', 'ausente')");
        }
        if (!DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'estado_categoria'")) {
            DB::statement("CREATE TYPE estado_categoria AS ENUM ('activa', 'inactiva')");
        }
        if (!DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'estado_item'")) {
            DB::statement("CREATE TYPE estado_item AS ENUM ('activo', 'inactivo')");
        }
        if (!DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'dia_semana'")) {
            DB::statement("CREATE TYPE dia_semana AS ENUM ('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo')");
        }
        if (!DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'estado_horario'")) {
            DB::statement("CREATE TYPE estado_horario AS ENUM ('activo', 'inactivo')");
        }
        if (!DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'estado_reserva'")) {
            DB::statement("CREATE TYPE estado_reserva AS ENUM ('pendiente_pago', 'confirmada', 'en_proceso', 'completada', 'cancelada', 'no_asistio')");
        }
        if (!DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'metodo_pago'")) {
            DB::statement("CREATE TYPE metodo_pago AS ENUM ('efectivo', 'tarjeta', 'transferencia', 'otro')");
        }
        if (!DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'tipo_pago'")) {
            DB::statement("CREATE TYPE tipo_pago AS ENUM ('anticipo', 'pago_final', 'pago_completo')");
        }
        if (!DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'estado_pago'")) {
            DB::statement("CREATE TYPE estado_pago AS ENUM ('pendiente', 'pagado', 'cancelado', 'reembolsado')");
        }
        if (!DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'tipo_reporte'")) {
            DB::statement("CREATE TYPE tipo_reporte AS ENUM ('ventas', 'servicios', 'productos', 'barberos', 'clientes')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop PostgreSQL enum types in reverse dependency order
        DB::statement('DROP TYPE IF EXISTS tipo_reporte');
        DB::statement('DROP TYPE IF EXISTS estado_pago');
        DB::statement('DROP TYPE IF EXISTS tipo_pago');
        DB::statement('DROP TYPE IF EXISTS metodo_pago');
        DB::statement('DROP TYPE IF EXISTS estado_reserva');
        DB::statement('DROP TYPE IF EXISTS estado_horario');
        DB::statement('DROP TYPE IF EXISTS dia_semana');
        DB::statement('DROP TYPE IF EXISTS estado_item');
        DB::statement('DROP TYPE IF EXISTS estado_categoria');
        DB::statement('DROP TYPE IF EXISTS estado_barbero');
        DB::statement('DROP TYPE IF EXISTS tipo_usuario');
        DB::statement('DROP TYPE IF EXISTS estado_usuario');
    }
};

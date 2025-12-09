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
        Schema::create('horario', function (Blueprint $table) {
            $table->id('id_horario');
            $table->unsignedBigInteger('id_barbero');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();

            $table->foreign('id_barbero')
                ->references('id_barbero')->on('barbero')
                ->onDelete('cascade');
        });

        // PostgreSQL enums and constraints
        DB::statement("ALTER TABLE horario ADD COLUMN dia_semana dia_semana NOT NULL");
        DB::statement("ALTER TABLE horario ADD COLUMN estado estado_horario DEFAULT 'activo'");
        DB::statement("CREATE UNIQUE INDEX uk_horario_barbero_dia ON horario (id_barbero, dia_semana, hora_inicio)");
        DB::statement("CREATE INDEX idx_horario_barbero ON horario (id_barbero)");
        DB::statement("CREATE INDEX idx_horario_dia ON horario (dia_semana)");
        DB::statement("ALTER TABLE horario ADD CONSTRAINT chk_horario_valido CHECK (hora_fin > hora_inicio)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario');
    }
};

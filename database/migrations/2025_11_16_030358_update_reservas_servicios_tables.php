<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up()
    {
        // Actualizar la tabla reserva sin tocar índices existentes
        Schema::table('reserva', function (Blueprint $table) {
            // Solo cambiar los tipos de datos si es necesario
            // PostgreSQL maneja mejor los cambios de tipo con USING
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Sin cambios en down()
    }
};

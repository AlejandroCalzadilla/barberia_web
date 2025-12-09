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
        Schema::create('barbero', function (Blueprint $table) {
            $table->id('id_barbero');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('especialidad', 100)->nullable();
            $table->string('foto_perfil', 255)->nullable();
            $table->decimal('calificacion_promedio', 3, 2)->default(0);
            $table->timestamps();

            $table->foreign('id_usuario')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });

        DB::statement("ALTER TABLE barbero ADD COLUMN estado estado_barbero DEFAULT 'disponible'");
        DB::statement("CREATE INDEX idx_barbero_usuario ON barbero (id_usuario)");
        DB::statement("CREATE INDEX idx_barbero_estado ON barbero (estado)");
        DB::statement("ALTER TABLE barbero ADD CONSTRAINT chk_calificacion CHECK (calificacion_promedio >= 0 AND calificacion_promedio <= 5)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barbero');
    }
};

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
        Schema::create('servicio', function (Blueprint $table) {
            $table->id('id_servicio');
            
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->integer('duracion_minutos');
            $table->decimal('precio', 10, 2);
            $table->string('imagen', 255)->nullable();
            $table->timestamps();

            });

        DB::statement("ALTER TABLE servicio ADD COLUMN estado estado_item DEFAULT 'activo'");
        DB::statement("CREATE INDEX idx_servicio_estado ON servicio (estado)");
        DB::statement("ALTER TABLE servicio ADD CONSTRAINT chk_duracion CHECK (duracion_minutos > 0)");
        DB::statement("ALTER TABLE servicio ADD CONSTRAINT chk_precio_servicio CHECK (precio >= 0)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicio');
    }
};

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
        Schema::create('reserva', function (Blueprint $table) {
            $table->id('id_reserva');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_barbero');
            $table->unsignedBigInteger('id_servicio');
            $table->date('fecha_reserva');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->text('notas')->nullable();
            $table->decimal('total', 10, 2);
            $table->decimal('monto_anticipo', 10, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('id_cliente')->references('id_cliente')->on('cliente')->onDelete('restrict');
            $table->foreign('id_barbero')->references('id_barbero')->on('barbero')->onDelete('restrict');
            $table->foreign('id_servicio')->references('id_servicio')->on('servicio')->onDelete('restrict');
        });

        DB::statement("ALTER TABLE reserva ADD COLUMN estado estado_reserva DEFAULT 'pendiente_pago'");
        DB::statement("CREATE INDEX idx_reserva_cliente ON reserva (id_cliente)");
        DB::statement("CREATE INDEX idx_reserva_barbero ON reserva (id_barbero)");
        DB::statement("CREATE INDEX idx_reserva_servicio ON reserva (id_servicio)");
        DB::statement("CREATE INDEX idx_reserva_fecha ON reserva (fecha_reserva)");
        DB::statement("CREATE INDEX idx_reserva_estado ON reserva (estado)");
        DB::statement("ALTER TABLE reserva ADD CONSTRAINT chk_reserva_horario CHECK (hora_fin > hora_inicio)");
        DB::statement("ALTER TABLE reserva ADD CONSTRAINT chk_total_reserva CHECK (total >= 0)");
        DB::statement("ALTER TABLE reserva ADD CONSTRAINT chk_monto_anticipo CHECK (monto_anticipo >= 0)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserva');
    }
};

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
        Schema::create('pago', function (Blueprint $table) {
            $table->id('id_pago');
            $table->unsignedBigInteger('id_reserva');
            $table->decimal('monto_total', 10, 2);
            $table->timestamp('fecha_pago')->useCurrent();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('id_reserva')
                ->references('id_reserva')->on('reserva')
                ->onDelete('restrict');
        });

        // PostgreSQL enums
        DB::statement("ALTER TABLE pago ADD COLUMN metodo_pago metodo_pago NOT NULL");
        DB::statement("ALTER TABLE pago ADD COLUMN tipo_pago tipo_pago NOT NULL");
        DB::statement("ALTER TABLE pago ADD COLUMN estado estado_pago DEFAULT 'pendiente'");

        // Indexes
        DB::statement("CREATE INDEX idx_pago_reserva ON pago (id_reserva)");
        DB::statement("CREATE INDEX idx_pago_fecha ON pago (fecha_pago)");
        DB::statement("CREATE INDEX idx_pago_estado ON pago (estado)");
        DB::statement("CREATE INDEX idx_pago_tipo ON pago (tipo_pago)");

        // Checks
        DB::statement("ALTER TABLE pago ADD CONSTRAINT chk_monto_total CHECK (monto_total >= 0)");
       }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago');
    }
};

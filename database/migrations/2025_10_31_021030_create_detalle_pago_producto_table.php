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
        Schema::create('detalle_pago_producto', function (Blueprint $table) {
            $table->id('id_detalle');
            $table->unsignedBigInteger('id_pago');
            $table->unsignedBigInteger('id_producto');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            $table->foreign('id_pago')->references('id_pago')->on('pago')->onDelete('cascade');
            $table->foreign('id_producto')->references('id_producto')->on('producto')->onDelete('restrict');
        });

        DB::statement("CREATE INDEX idx_detalle_pago ON detalle_pago_producto (id_pago)");
        DB::statement("CREATE INDEX idx_detalle_producto ON detalle_pago_producto (id_producto)");
        DB::statement("ALTER TABLE detalle_pago_producto ADD CONSTRAINT chk_cantidad CHECK (cantidad > 0)");
        DB::statement("ALTER TABLE detalle_pago_producto ADD CONSTRAINT chk_precio_unitario CHECK (precio_unitario >= 0)");
        DB::statement("ALTER TABLE detalle_pago_producto ADD CONSTRAINT chk_subtotal CHECK (subtotal >= 0)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pago_producto');
    }
};

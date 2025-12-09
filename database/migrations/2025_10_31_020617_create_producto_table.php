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
        Schema::create('producto', function (Blueprint $table) {
            $table->id('id_producto');
            $table->unsignedBigInteger('id_categoria');
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio_compra', 10, 2)->default(0.00);
            $table->decimal('precio_venta', 10, 2);
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->string('unidad_medida', 20)->nullable();
            $table->string('imagenurl', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_categoria')
                ->references('id_categoria')->on('categoria')
                ->onDelete('restrict');
        });

        // ENUM estado_item
        DB::statement("ALTER TABLE producto ADD COLUMN estado estado_item DEFAULT 'activo'");
        // Indexes
        DB::statement("CREATE INDEX idx_producto_categoria ON producto (id_categoria)");
        DB::statement("CREATE INDEX idx_producto_estado ON producto (estado)");
        DB::statement("CREATE INDEX idx_producto_stock ON producto (stock_actual)");
        // Checks
        DB::statement("ALTER TABLE producto ADD CONSTRAINT chk_precio_compra CHECK (precio_compra >= 0)");
        DB::statement("ALTER TABLE producto ADD CONSTRAINT chk_precio_venta CHECK (precio_venta >= 0)");
        DB::statement("ALTER TABLE producto ADD CONSTRAINT chk_stock_actual CHECK (stock_actual >= 0)");
        DB::statement("ALTER TABLE producto ADD CONSTRAINT chk_stock_minimo CHECK (stock_minimo >= 0)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};

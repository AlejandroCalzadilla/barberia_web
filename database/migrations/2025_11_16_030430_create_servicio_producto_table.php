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
        Schema::create('servicio_producto', function (Blueprint $table) {
            $table->unsignedBigInteger('id_servicio');
            $table->unsignedBigInteger('id_producto');
            $table->integer('cantidad')->default(1);
            
            $table->primary(['id_servicio', 'id_producto']);
            
            $table->foreign('id_servicio')
                  ->references('id_servicio')
                  ->on('servicio')
                  ->onDelete('cascade');
                  
            $table->foreign('id_producto')
                  ->references('id_producto')
                  ->on('producto')
                  ->onDelete('cascade');
                  
            $table->timestamps();
        });
        
        // Add indexes for better performance
        if (!Schema::hasIndex('servicio_producto', 'idx_servicio_producto_servicio')) {
            DB::statement("CREATE INDEX idx_servicio_producto_servicio ON servicio_producto (id_servicio)");
        }
        if (!Schema::hasIndex('servicio_producto', 'idx_servicio_producto_producto')) {
            DB::statement("CREATE INDEX idx_servicio_producto_producto ON servicio_producto (id_producto)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicio_producto');
    }
};

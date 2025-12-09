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
    public function up()
    {
        // Drop existing constraints and indexes
        Schema::table('reserva', function (Blueprint $table) {
            $table->dropForeign(['id_cliente']);
            $table->dropForeign(['id_barbero']);
            $table->dropForeign(['id_servicio']);
            
            // Drop existing indexes
            $table->dropIndex('idx_reserva_cliente');
            $table->dropIndex('idx_reserva_barbero');
            $table->dropIndex('idx_reserva_servicio');
            $table->dropIndex('idx_reserva_fecha');
            $table->dropIndex('idx_reserva_estado');
        });

        // Modify the reserva table
        Schema::table('reserva', function (Blueprint $table) {
            // Change column types to match the new schema
            $table->string('fecha_reserva', 15)->change();
            $table->string('hora_inicio', 10)->change();
            $table->string('hora_fin', 10)->change();
            
            // Add new columns
            
            // Check if created_at already exists before renaming
            if (Schema::hasColumn('reserva', 'fecha_creacion') && !Schema::hasColumn('reserva', 'created_at')) {
                $table->renameColumn('fecha_creacion', 'created_at');
            }
            
            
            
            // Add foreign key constraints with CASCADE on delete
            $table->foreign('id_cliente')
                  ->references('id_cliente')
                  ->on('cliente')
                  ->onDelete('cascade');
                  
            $table->foreign('id_barbero')
                  ->references('id_barbero')
                  ->on('barbero')
                  ->onDelete('cascade');
                  
            $table->foreign('id_servicio')
                  ->references('id_servicio')
                  ->on('servicio')
                  ->onDelete('cascade');
        });
        
        // Recreate indexes with new names
        DB::statement("CREATE INDEX idx_reserva_cliente ON reserva (id_cliente)");
        DB::statement("CREATE INDEX idx_reserva_barbero ON reserva (id_barbero)");
        DB::statement("CREATE INDEX idx_reserva_servicio ON reserva (id_servicio)");
        DB::statement("CREATE INDEX idx_reserva_fecha ON reserva (fecha_reserva)");
        DB::statement("CREATE INDEX idx_reserva_estado ON reserva (estado)");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop indexes
        Schema::table('reserva', function (Blueprint $table) {
            $table->dropIndex('idx_reserva_cliente');
            $table->dropIndex('idx_reserva_barbero');
            $table->dropIndex('idx_reserva_servicio');
            $table->dropIndex('idx_reserva_fecha');
            $table->dropIndex('idx_reserva_estado');
            
            // Drop foreign keys
            $table->dropForeign(['id_cliente']);
            $table->dropForeign(['id_barbero']);
            $table->dropForeign(['id_servicio']);
        });
        
        // Revert column changes
        Schema::table('reserva', function (Blueprint $table) {
            $table->date('fecha_reserva')->change();
            $table->time('hora_inicio')->change();
            $table->time('hora_fin')->change();
            
            // Revert column names and add back dropped columns
            $table->renameColumn('created_at', 'fecha_creacion');
            
            // Drop the added column
            
        });
        
        // Recreate original foreign keys
        Schema::table('reserva', function (Blueprint $table) {
            $table->foreign('id_cliente')
                  ->references('id_cliente')
                  ->on('client')
                  ->onDelete('restrict');
                  
            $table->foreign('id_barbero')
                  ->references('id_barbero')
                  ->on('barbero')
                  ->onDelete('restrict');
                  
            $table->foreign('id_servicio')
                  ->references('id_servicio')
                  ->on('servicio')
                  ->onDelete('restrict');
        });
        
        // Recreate original indexes
        DB::statement("CREATE INDEX idx_reserva_cliente ON reserva (id_cliente)");
        DB::statement("CREATE INDEX idx_reserva_barbero ON reserva (id_barbero)");
        DB::statement("CREATE INDEX idx_reserva_servicio ON reserva (id_servicio)");
        DB::statement("CREATE INDEX idx_reserva_fecha ON reserva (fecha_reserva)");
        DB::statement("CREATE INDEX idx_reserva_estado ON reserva (estado)");
    }
};

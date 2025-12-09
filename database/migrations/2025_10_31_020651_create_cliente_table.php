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
        Schema::create('cliente', function (Blueprint $table) {
            $table->id('id_cliente');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('ci', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });

        DB::statement("CREATE INDEX idx_cliente_usuario ON cliente (id_usuario)");
        DB::statement("ALTER TABLE cliente ADD CONSTRAINT chk_fecha_nacimiento CHECK (fecha_nacimiento IS NULL OR fecha_nacimiento <= CURRENT_DATE)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente');
    }
};

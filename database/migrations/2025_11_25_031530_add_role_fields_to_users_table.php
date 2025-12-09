<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_propietario')->default(false)->after('profile_photo_path');
            $table->boolean('is_barbero')->default(false)->after('is_propietario');
            $table->boolean('is_cliente')->default(false)->after('is_barbero');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_propietario', 'is_barbero', 'is_cliente']);
        });
    }
};

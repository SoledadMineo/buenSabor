<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No se pueden deducir el producto y el insumo de una cantidad aislada.
        if (DB::table('articulo_manufacturado_detalle')->exists()) {
            throw new RuntimeException('Hay detalles existentes: asignar su producto e insumo antes de aplicar esta migración.');
        }

        Schema::table('articulo_manufacturado_detalle', function (Blueprint $table) {
            $table->foreignId('articulo_manufacturado_id')->constrained('articulo_manufacturado')->restrictOnDelete();
            $table->foreignId('articulo_insumo_id')->constrained('articulo_insumo')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('articulo_manufacturado_detalle', function (Blueprint $table) {
            $table->dropConstrainedForeignId('articulo_insumo_id');
            $table->dropConstrainedForeignId('articulo_manufacturado_id');
        });
    }
};

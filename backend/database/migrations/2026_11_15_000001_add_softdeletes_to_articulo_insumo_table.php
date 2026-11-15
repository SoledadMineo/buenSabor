<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Baja lógica (SoftDeletes): un insumo dado de baja deja de ofrecerse, pero se
        // conservan recetas, pedidos y facturas que lo referencian.
        Schema::table('articulo_insumo', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Un insumo solo puede tener un registro de stock por sucursal.
        Schema::table('sucursal_insumo', function (Blueprint $table) {
            $table->unique(['sucursal_empresa_id', 'articulo_insumo_id'], 'sucursal_insumo_unico');
        });
    }

    public function down(): void
    {
        Schema::table('sucursal_insumo', function (Blueprint $table) {
            $table->dropUnique('sucursal_insumo_unico');
        });

        Schema::table('articulo_insumo', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

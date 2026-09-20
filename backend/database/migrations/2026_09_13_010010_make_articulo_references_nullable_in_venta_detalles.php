<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_venta_detalle', function (Blueprint $table) {
            $table->unsignedBigInteger('articulo_insumo_id')->nullable()->change();
        });

        Schema::table('factura_venta_detalle', function (Blueprint $table) {
            $table->unsignedBigInteger('articulo_manufacturado_id')->nullable()->change();
            $table->unsignedBigInteger('articulo_insumo_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pedido_venta_detalle', function (Blueprint $table) {
            $table->unsignedBigInteger('articulo_insumo_id')->nullable(false)->change();
        });

        Schema::table('factura_venta_detalle', function (Blueprint $table) {
            $table->unsignedBigInteger('articulo_manufacturado_id')->nullable(false)->change();
            $table->unsignedBigInteger('articulo_insumo_id')->nullable(false)->change();
        });
    }
};

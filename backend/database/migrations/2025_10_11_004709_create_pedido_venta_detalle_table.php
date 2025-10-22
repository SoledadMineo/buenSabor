<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_venta_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_venta_id')->constrained('pedido_venta')->onDelete('cascade');
            $table->foreignId('articulo_manufacturado_id')->nullable()->constrained('articulo_manufacturado')->onDelete('set null');
            $table->foreignId('promocion_id')->nullable()->constrained('promocion')->onDelete('set null');
            $table->foreignId('articulo_insumo_id')->constrained('articulo_insumo')->onDelete('cascade');
            $table->double('cantidad');
            $table->double('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_venta_detalle');
    }
};

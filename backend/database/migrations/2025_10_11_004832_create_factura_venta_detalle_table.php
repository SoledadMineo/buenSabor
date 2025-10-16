<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_venta_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_venta_id')->constrained('pedido_venta')->onDelete('cascade');
            $table->foreignId('factura_venta_id')->constrained('factura_venta')->onDelete('cascade');
            $table->double('cantidad');
            $table->double('sub_total');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_venta_detalle');
    }
};

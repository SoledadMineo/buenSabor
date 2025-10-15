<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_venta', function (Blueprint $table) {
            $table->id();
            $table->integer('horaEstimadaFinalizacion');
            $table->double('subtotal');
            $table->double('descuento');
            $table->double('gastosEnvio');
            $table->double('total');
            $table->double('totalCosto');
            $table->enum('estado', [
                'preparacion',
                'pendiente',
                'cancelado',
                'rechazado',
                'entregado'
                ])->default('pendiente');
            $table->enum('tipoEnvio', [
                'delivery',
                'takeAway'
                ])->default('delivery');
            $table->enum('formaPago', [
                'efectivo',
                'MercadoPago'
                ])->default('efectivo');
            $table->date('fechaPedido');
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('empleado_id')->constrained('empleado')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_venta');
    }
};

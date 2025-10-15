<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_venta', function (Blueprint $table) {
            $table->id();
            $table->date('fechaFacturacion');
            $table->integer('numeroComprobante');
            $table->enum('formaPago', [
                'efectivo',
                'MercadoPago'
                ])->default('efectivo');
            $table->double('subtotal');
            $table->double('descuento');
            $table->double('gastosEnvio');
            $table->double('totalVenta');
            $table->foreignId('pedido_venta_id')->constrained('pedido_venta')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_venta');
    }
};

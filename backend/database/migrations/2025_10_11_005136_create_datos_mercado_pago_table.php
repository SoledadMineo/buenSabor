<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datos_mercado_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_venta_id')->constrained('factura_venta')->onDelete('cascade');
            $table->date('date_created');
            $table->date('date_approved');
            $table->date('date_last_updated');
            $table->integer('payment_type_id');
            $table->integer('payment_method_id');
            $table->string('status');
            $table->string('status_detail');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datos_mercado_pago');
    }
};

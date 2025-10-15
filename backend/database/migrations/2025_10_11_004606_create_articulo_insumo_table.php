<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articulo_insumo', function (Blueprint $table) {
            $table->id();
            $table->string('denominacio');
            $table->double('precioCompra');
            $table->double('precioVenta');
            $table->boolean('esParaElaborar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articulo_insumo');
    }
};

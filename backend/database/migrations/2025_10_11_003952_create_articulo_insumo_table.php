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
            $table->foreignId('categoria_articulo_id')->constrained('categoria_articulo')->onDelete('cascade');
            $table->foreignId('imagen_insumo_id')->constrained('imagen_insumo')->onDelete('cascade');
            $table->foreignId('unidad_medida_id')->constrained('unidad_medida')->onDelete('cascade');
            $table->foreignId('padre_id')->nullable()->constrained('articulo_insumo')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articulo_insumo');
    }
};

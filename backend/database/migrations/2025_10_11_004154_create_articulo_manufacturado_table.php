<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articulo_manufacturado', function (Blueprint $table) {
            $table->id();
            $table->string('denominacion');
            $table->string('descripcion');
            $table->double('precioVenta');
            $table->double('precioCosto');
            $table->integer('tiempoEstimado');
            $table->foreignId('categoria_id')->constrained('categoria_articulo_manufacturado')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articulo_manufacturado');
    }
};

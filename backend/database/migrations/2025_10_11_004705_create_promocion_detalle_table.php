<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promocion_detalle', function (Blueprint $table) {
            $table->id();
            $table->integer('cantidad');
            $table->foreignId('promocion_id')->constrained('promocion')->onDelete('cascade');
            $table->foreignId('articulo_manufacturado_id')->constrained('articulo_manufacturado')->onDelete('cascade');
            $table->foreignId('articulo_insumo_id')->constrained('articulo_insumo')->onDelete('cascade');            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promocion_detalle');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sucursal_insumo', function (Blueprint $table) {
            $table->id();
            $table->integer('stockActual');
            $table->integer('stockMinimo');
            $table->integer('stockMaximo');
            $table->foreign('sucursal_empresa_id')->references('id')->on('sucursal_empresas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursal_insumo');
    }
};

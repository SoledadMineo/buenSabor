<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domicilio', function (Blueprint $table) {
            $table->id();
            $table->string('calle');
            $table->integer('numero');
            $table->integer('cp');
            $table->foreignId('sucursalEmpresa_id')->constrained('sucursal_empresa');
            $table->foreignId('localidad_id')->constrained('localidad');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domicilio');
    }
};

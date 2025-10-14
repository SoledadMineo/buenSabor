<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sucursal_empresa', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('horarioApertura');
            $table->string('horarioCierre');
            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->onDelete('cascade');
            $table->foreignId('domicilio_id')->constrained('domicilios');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursal_empresa');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articulo_manufacturado_detalle', function (Blueprint $table) {
            $table->id();
            $table->double('cantidad');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articulo_manufacturado_detalle');
    }
};

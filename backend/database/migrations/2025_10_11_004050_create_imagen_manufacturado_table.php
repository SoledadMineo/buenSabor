<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imagen_manufacturado', function (Blueprint $table) {
            $table->id();
            $table->string('denominacion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagen_manufacturado');
    }
};

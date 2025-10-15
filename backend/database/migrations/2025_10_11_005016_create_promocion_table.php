<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promocion', function (Blueprint $table) {
            $table->id();
            $table->string('denominacion');
            $table->date('fechaDesde');
            $table->date('fechaHasta');
            $table->double('descuento');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promocion');
    }
};

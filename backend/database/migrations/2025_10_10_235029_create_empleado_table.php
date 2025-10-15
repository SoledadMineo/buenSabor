<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleado', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->integer('telefono');
            $table->string('email');
            $table->enum('rol', [
                'admin',
                'empleado',
                'cliente'])->default('admin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleado');
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_venta', function (Blueprint $table) {
            $table->foreignId('cliente_id')
                ->nullable()
                ->constrained('cliente')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pedido_venta', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cliente_id');
        });
    }
};

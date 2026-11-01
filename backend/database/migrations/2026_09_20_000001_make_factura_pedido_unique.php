<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hayDuplicados = DB::table('factura_venta')
            ->select('pedido_venta_id')
            ->groupBy('pedido_venta_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hayDuplicados) {
            throw new RuntimeException('Hay pedidos con varias facturas. Revisar esos registros antes de aplicar la restricción; no se eliminaron datos.');
        }

        Schema::table('factura_venta', function (Blueprint $table) {
            $table->unique('pedido_venta_id');
        });
    }

    public function down(): void
    {
        Schema::table('factura_venta', function (Blueprint $table) {
            $table->dropUnique(['pedido_venta_id']);
        });
    }
};

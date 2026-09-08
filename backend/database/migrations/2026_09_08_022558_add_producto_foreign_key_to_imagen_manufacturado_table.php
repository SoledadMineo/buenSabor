<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $relacionExiste = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', 'imagen_manufacturado')
            ->where('COLUMN_NAME', 'articulo_manufacturado_id')
            ->where('REFERENCED_TABLE_NAME', 'articulo_manufacturado')
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->exists();

        if (!$relacionExiste) {
            Schema::table('imagen_manufacturado', function (Blueprint $table) {
                $table->foreign('articulo_manufacturado_id')
                    ->references('id')
                    ->on('articulo_manufacturado')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('imagen_manufacturado', function (Blueprint $table) {
            $table->dropForeign(['articulo_manufacturado_id']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('usuario')->select('username')->groupBy('username')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Hay nombres de usuario duplicados. Resolverlos antes de habilitar el login local; no se modificaron las cuentas.');
        }

        Schema::table('usuario', function (Blueprint $table) {
            $table->string('authOId')->nullable()->change();
            // Las cuentas existentes deberán establecer su contraseña antes de ingresar.
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->unique('username');
        });
    }

    public function down(): void
    {
        if (DB::table('usuario')->whereNull('authOId')->exists()) {
            throw new RuntimeException('Hay usuarios sin authOId. No se puede restaurar el esquema anterior sin resolver esas cuentas.');
        }

        Schema::table('usuario', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['password', 'remember_token']);
            $table->string('authOId')->nullable(false)->change();
        });
    }
};

<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\Cliente;
use App\Models\Domicilio;
use App\Models\Empleado;
use App\Models\Localidad;
use App\Models\Pais;
use App\Models\Provincia;
use App\Models\SucursalEmpresa;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $pais = Pais::firstOrCreate(['nombre' => 'Argentina']);
            $provincia = Provincia::firstOrCreate([
                'nombre' => 'Mendoza', 'pais_id' => $pais->id,
            ]);
            $localidad = Localidad::firstOrCreate([
                'nombre' => 'Ciudad de Mendoza', 'provincia_id' => $provincia->id,
            ]);

            // La tabla de la migracion es "empresas".
            $empresaId = DB::table('empresas')->where('cuil', 30712345678)->value('id');
            if (!$empresaId) {
                $empresaId = DB::table('empresas')->insertGetId([
                    'nombre' => 'El Buen Sabor',
                    'razonSocial' => 'El Buen Sabor Demo',
                    'cuil' => 30712345678,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $sucursal = SucursalEmpresa::firstOrCreate([
                'empresa_id' => $empresaId, 'nombre' => 'Sucursal de prueba',
            ], ['horarioApertura' => '09:00', 'horarioCierre' => '23:00']);

            // firstOrCreate permite repetir la carga y conserva las claves existentes.
            for ($i = 1; $i <= 15; $i++) {
                $usuario = Usuario::firstOrCreate(['username' => "cliente$i"], [
                    'password' => 'Prueba123!',
                ]);

                $domicilio = Domicilio::firstOrNew([
                    'calle' => 'San Martin', 'numero' => 1000 + $i,
                    'localidad_id' => $localidad->id,
                ]);
                if (!$domicilio->exists) {
                    $domicilio->cp = 5500;
                    $domicilio->sucursalEmpresa_id = $sucursal->id;
                    $domicilio->save();
                }

                Cliente::firstOrCreate(['usuario_id' => $usuario->id], [
                    'nombre' => "Cliente $i", 'apellido' => 'Prueba',
                    'telefono' => 261500000 + $i,
                    'email' => "cliente$i@example.com",
                    'domicilio_id' => $domicilio->id,
                ]);
            }

            for ($i = 1; $i <= 5; $i++) {
                $usuario = Usuario::firstOrCreate(['username' => "empleado$i"], [
                    'password' => 'Prueba123!',
                ]);
                Empleado::firstOrCreate(['usuario_id' => $usuario->id], [
                    'nombre' => "Empleado $i", 'apellido' => 'Prueba',
                    'telefono' => 261600000 + $i,
                    'email' => "empleado$i@example.com",
                    'rol' => Rol::EMPLEADO,
                ]);
            }
        });
    }
}

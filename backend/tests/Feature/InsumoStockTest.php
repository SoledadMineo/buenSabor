<?php

namespace Tests\Feature;

use App\Enums\Rol;
use App\Models\ArticuloInsumo;
use App\Models\CategoriaArticulo;
use App\Models\Empleado;
use App\Models\SucursalInsumo;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InsumoStockTest extends TestCase
{
    private int $sucursalId;

    protected function setUp(): void
    {
        parent::setUp();

        // Conexión exclusiva: nunca ejecutar migraciones sobre la base del .env.
        config([
            'database.default' => 'insumos_test',
            'database.connections.insumos_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('insumos_test');

        // Esquema final de usuario (la migración usa change(), que SQLite no soporta sin DBAL).
        Schema::create('usuario', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('authOId')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        foreach ([
            '2025_10_10_120150_create_empresas_table',
            '2025_10_10_120631_create_sucursal_empresa_table',
            '2025_10_10_235029_create_empleado_table',
            '2025_10_11_003948_create_unidad_medida_table',
            '2025_10_11_003949_create_imagen_insumo_table',
            '2025_10_11_003951_create_categoria_articulo_table',
            '2025_10_11_003952_create_articulo_insumo_table',
            '2025_10_11_003953_create_sucursal_insumo_table',
            '2025_10_11_004133_create_categoria_articulo_manufacturado_table',
            '2025_10_11_004154_create_articulo_manufacturado_table',
            '2025_10_11_004627_create_articulo_manufacturado_detalle_table',
            '2026_09_07_000001_add_relations_to_articulo_manufacturado_detalle',
            '2026_11_15_000001_add_softdeletes_to_articulo_insumo_table',
            '2026_11_16_000001_add_softdeletes_to_articulo_manufacturado_table',
        ] as $name) {
            (require database_path("migrations/{$name}.php"))->up();
        }

        DB::table('empresas')->insert(['id' => 1, 'nombre' => 'Prueba', 'razonSocial' => 'Prueba', 'cuil' => 20123456789]);
        $this->sucursalId = DB::table('sucursal_empresa')->insertGetId(['empresa_id' => 1, 'nombre' => 'Central', 'horarioApertura' => '09:00', 'horarioCierre' => '23:00']);
    }

    private function comoRol(Rol $rol): void
    {
        $usuario = Usuario::create(['username' => $rol->value, 'password' => 'ClaveDePrueba123!']);
        Empleado::create([
            'usuario_id' => $usuario->id, 'nombre' => 'A', 'apellido' => 'B',
            'telefono' => 1, 'email' => "{$rol->value}@example.com", 'rol' => $rol,
        ]);
        $this->actingAs($usuario, 'web');
    }

    private function insumo(string $nombre = 'Harina', bool $elaborar = true): ArticuloInsumo
    {
        return ArticuloInsumo::create([
            'denominacion' => $nombre, 'precioCompra' => 10, 'precioVenta' => 15,
            'esParaElaborar' => $elaborar,
            'categoria_articulo_id' => CategoriaArticulo::firstOrCreate(['denominacion' => 'Almacén'])->id,
            'unidad_medida_id' => UnidadMedida::firstOrCreate(['denominacion' => 'Kilogramo'])->id,
        ]);
    }

    public function test_filtra_insumos_de_elaboracion_y_de_venta(): void
    {
        $this->insumo('Harina', true);
        $this->insumo('Coca-Cola', false);

        $this->getJson('/api/insumos?tipo=elaboracion')->assertOk()->assertJsonCount(1)
            ->assertJsonPath('0.denominacion', 'Harina');
        $this->getJson('/api/insumos?tipo=venta')->assertOk()->assertJsonCount(1)
            ->assertJsonPath('0.denominacion', 'Coca-Cola');
        $this->getJson('/api/insumos')->assertOk()->assertJsonCount(2);
    }

    public function test_solo_admin_modifica_insumos_y_stock(): void
    {
        $insumo = $this->insumo();

        $this->deleteJson("/api/insumos/{$insumo->id}")->assertStatus(401);

        $this->comoRol(Rol::EMPLEADO);
        $this->deleteJson("/api/insumos/{$insumo->id}")->assertStatus(403);
        $this->putJson("/api/insumos/{$insumo->id}/stock", [])->assertStatus(403);
        $this->assertNull($insumo->fresh()->deleted_at);
    }

    public function test_baja_logica_oculta_y_se_puede_restaurar(): void
    {
        $this->comoRol(Rol::ADMIN);
        $insumo = $this->insumo();

        $this->deleteJson("/api/insumos/{$insumo->id}")->assertOk();
        $this->getJson('/api/insumos')->assertOk()->assertJsonCount(0);
        $this->assertNotNull(ArticuloInsumo::withTrashed()->find($insumo->id));
        $this->getJson('/api/articulos/admin?baja=1')->assertOk()->assertJsonCount(1);

        $this->postJson("/api/insumos/{$insumo->id}/restaurar")->assertOk();
        $this->getJson('/api/insumos')->assertOk()->assertJsonCount(1);
    }

    public function test_edita_insumo_pero_no_pasa_a_venta_si_esta_en_una_receta(): void
    {
        $this->comoRol(Rol::ADMIN);
        $insumo = $this->insumo();
        $datos = [
            'denominacion' => 'Harina 0000', 'precioCompra' => 20, 'precioVenta' => 30,
            'esParaElaborar' => 1,
            'categoria_articulo_id' => $insumo->categoria_articulo_id,
            'unidad_medida_id' => $insumo->unidad_medida_id,
        ];
        $this->putJson("/api/insumos/{$insumo->id}", $datos)->assertOk()
            ->assertJsonPath('denominacion', 'Harina 0000');

        $this->putJson("/api/insumos/{$insumo->id}", ['precioCompra' => -1] + $datos)->assertStatus(422);

        $manufacturado = $this->manufacturado();
        DB::table('articulo_manufacturado_detalle')->insert([
            'cantidad' => 1, 'articulo_manufacturado_id' => $manufacturado, 'articulo_insumo_id' => $insumo->id,
        ]);
        $this->putJson("/api/insumos/{$insumo->id}", ['esParaElaborar' => 0] + $datos)->assertStatus(422);
    }

    private function manufacturado(): int
    {
        $categoria = DB::table('categoria_articulo_manufacturado')->insertGetId(['denominacion' => 'Pizzas']);

        return DB::table('articulo_manufacturado')->insertGetId([
            'denominacion' => 'Pizza', 'descripcion' => 'Pizza', 'precioVenta' => 1, 'precioCosto' => 1,
            'tiempoEstimado' => 1, 'categoria_id' => $categoria,
        ]);
    }

    public function test_categoria_y_unidad_en_uso_no_se_eliminan(): void
    {
        $this->comoRol(Rol::ADMIN);
        $insumo = $this->insumo();

        $this->deleteJson("/api/categorias-articulo/{$insumo->categoria_articulo_id}")->assertStatus(409);
        $this->deleteJson("/api/unidades-medida/{$insumo->unidad_medida_id}")->assertStatus(409);

        $nueva = $this->postJson('/api/categorias-articulo', ['denominacion' => 'Verduras'])->assertCreated()->json('id');
        $this->postJson('/api/categorias-articulo', ['denominacion' => 'Verduras'])->assertStatus(422);
        $this->deleteJson("/api/categorias-articulo/{$nueva}")->assertOk();
    }

    public function test_stock_define_ajusta_sin_negativos_y_alerta(): void
    {
        $this->comoRol(Rol::ADMIN);
        $insumo = $this->insumo();
        $base = ['sucursal_empresa_id' => $this->sucursalId];

        $this->putJson("/api/insumos/{$insumo->id}/stock", $base + ['stockActual' => 10, 'stockMinimo' => 5, 'stockMaximo' => 20])
            ->assertCreated();
        $this->putJson("/api/insumos/{$insumo->id}/stock", $base + ['stockActual' => 10, 'stockMinimo' => 5, 'stockMaximo' => 2])
            ->assertStatus(422);

        $this->postJson("/api/insumos/{$insumo->id}/stock/ajuste", $base + ['cantidad' => -3])
            ->assertOk()->assertJsonPath('stockActual', 7);
        $this->postJson("/api/insumos/{$insumo->id}/stock/ajuste", $base + ['cantidad' => -8])->assertStatus(422);
        $this->postJson("/api/insumos/{$insumo->id}/stock/ajuste", $base + ['cantidad' => 14])->assertStatus(422);
        $this->assertSame(7, SucursalInsumo::first()->stockActual);

        $this->getJson('/api/stock/alertas')->assertOk()->assertJsonCount(0);
        $this->postJson("/api/insumos/{$insumo->id}/stock/ajuste", $base + ['cantidad' => -3])->assertOk();
        $this->getJson('/api/stock/alertas')->assertOk()->assertJsonCount(1);
    }
}

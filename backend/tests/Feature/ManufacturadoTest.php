<?php

namespace Tests\Feature;

use App\Enums\Rol;
use App\Models\ArticuloInsumo;
use App\Models\ArticuloManufacturado;
use App\Models\CategoriaArticulo;
use App\Models\Empleado;
use App\Models\SucursalInsumo;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManufacturadoTest extends TestCase
{
    private int $sucursalId;
    private int $categoriaId;

    protected function setUp(): void
    {
        parent::setUp();

        // Conexión exclusiva: nunca ejecutar migraciones sobre la base del .env.
        config([
            'database.default' => 'manufacturados_test',
            'database.connections.manufacturados_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('manufacturados_test');

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
            '2025_10_11_003950_create_imagen_manufacturado_table',
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
        $this->sucursalId = DB::table('sucursal_empresa')->insertGetId([
            'empresa_id' => 1, 'nombre' => 'Central', 'horarioApertura' => '09:00', 'horarioCierre' => '23:00',
        ]);
        $this->categoriaId = DB::table('categoria_articulo_manufacturado')->insertGetId(['denominacion' => 'Pizzas']);
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

    // Crea un insumo con stock en la sucursal.
    private function insumo(string $nombre, float $precio, int $stock, bool $elaborar = true): ArticuloInsumo
    {
        $insumo = ArticuloInsumo::create([
            'denominacion' => $nombre, 'precioCompra' => $precio, 'precioVenta' => $precio * 2,
            'esParaElaborar' => $elaborar,
            'categoria_articulo_id' => CategoriaArticulo::firstOrCreate(['denominacion' => 'Almacén'])->id,
            'unidad_medida_id' => UnidadMedida::firstOrCreate(['denominacion' => 'Kilogramo'])->id,
        ]);
        SucursalInsumo::create([
            'articulo_insumo_id' => $insumo->id, 'sucursal_empresa_id' => $this->sucursalId,
            'stockActual' => $stock, 'stockMinimo' => 1, 'stockMaximo' => 1000,
        ]);

        return $insumo;
    }

    private function datos(array $ingredientes, array $extra = []): array
    {
        return $extra + [
            'denominacion' => 'Pizza muzzarella', 'descripcion' => 'Con muzzarella',
            'precioVenta' => 5000, 'tiempoEstimado' => 20, 'categoria_id' => $this->categoriaId,
            'ingredientes' => $ingredientes,
        ];
    }

    public function test_crea_con_receta_calcula_costo_y_disponibilidad(): void
    {
        $this->comoRol(Rol::ADMIN);
        $harina = $this->insumo('Harina', 1000, 10);   // 0,2 kg por unidad -> 50 posibles
        $queso = $this->insumo('Queso', 8000, 3);      // 0,25 kg por unidad -> 12 posibles

        $r = $this->postJson('/api/manufacturados', $this->datos([
            ['articulo_insumo_id' => $harina->id, 'cantidad' => 0.2],
            ['articulo_insumo_id' => $queso->id, 'cantidad' => 0.25],
        ]))->assertCreated();

        // 0,2 * 1000 + 0,25 * 8000 = 2200
        $r->assertJsonPath('precioCosto', 2200)->assertJsonPath('costo_calculado', 2200)
            ->assertJsonPath('stock_posible', 12)->assertJsonPath('disponible', true)
            ->assertJsonCount(2, 'detalles');
        $this->assertSame(2, DB::table('articulo_manufacturado_detalle')->count());
    }

    public function test_valida_la_receta(): void
    {
        $this->comoRol(Rol::ADMIN);
        $harina = $this->insumo('Harina', 1000, 10);
        $bebida = $this->insumo('Coca-Cola', 900, 10, false);
        $baja = $this->insumo('Sal', 100, 10);
        $baja->delete();

        $this->postJson('/api/manufacturados', $this->datos([]))->assertStatus(422);
        $this->postJson('/api/manufacturados', $this->datos([['articulo_insumo_id' => $bebida->id, 'cantidad' => 1]]))
            ->assertStatus(422)->assertJsonValidationErrors('ingredientes.0.articulo_insumo_id');
        $this->postJson('/api/manufacturados', $this->datos([['articulo_insumo_id' => $baja->id, 'cantidad' => 1]]))
            ->assertStatus(422);
        $this->postJson('/api/manufacturados', $this->datos([['articulo_insumo_id' => $harina->id, 'cantidad' => 0]]))
            ->assertStatus(422);
        $this->postJson('/api/manufacturados', $this->datos([
            ['articulo_insumo_id' => $harina->id, 'cantidad' => 1],
            ['articulo_insumo_id' => $harina->id, 'cantidad' => 2],
        ]))->assertStatus(422);
        $this->assertSame(0, ArticuloManufacturado::count());
    }

    public function test_solo_admin_modifica_y_todos_consultan(): void
    {
        $harina = $this->insumo('Harina', 1000, 10);
        $datos = $this->datos([['articulo_insumo_id' => $harina->id, 'cantidad' => 1]]);

        $this->postJson('/api/manufacturados', $datos)->assertStatus(401);
        $this->comoRol(Rol::EMPLEADO);
        $this->postJson('/api/manufacturados', $datos)->assertStatus(403);
        $this->getJson('/api/manufacturados')->assertOk()->assertJsonCount(0);
    }

    public function test_actualiza_receta_y_el_precio_del_insumo_recalcula_el_costo(): void
    {
        $this->comoRol(Rol::ADMIN);
        $harina = $this->insumo('Harina', 1000, 10);
        $queso = $this->insumo('Queso', 8000, 10);

        $id = $this->postJson('/api/manufacturados', $this->datos([
            ['articulo_insumo_id' => $harina->id, 'cantidad' => 1],
        ]))->assertCreated()->json('id');

        $this->putJson("/api/manufacturados/{$id}", $this->datos([
            ['articulo_insumo_id' => $queso->id, 'cantidad' => 0.5],
        ], ['denominacion' => 'Pizza de queso']))->assertOk()
            ->assertJsonPath('denominacion', 'Pizza de queso')->assertJsonPath('precioCosto', 4000)
            ->assertJsonCount(1, 'detalles');
        $this->assertSame(1, DB::table('articulo_manufacturado_detalle')->count());

        // Si sube el precio del queso, el costo del producto se actualiza.
        $this->putJson("/api/insumos/{$queso->id}", [
            'denominacion' => 'Queso', 'precioCompra' => 10000, 'precioVenta' => 20000, 'esParaElaborar' => 1,
            'categoria_articulo_id' => $queso->categoria_articulo_id, 'unidad_medida_id' => $queso->unidad_medida_id,
        ])->assertOk();
        $this->assertEquals(5000, ArticuloManufacturado::find($id)->precioCosto);
    }

    public function test_sin_stock_no_esta_disponible(): void
    {
        $this->comoRol(Rol::ADMIN);
        $harina = $this->insumo('Harina', 1000, 1);

        $this->postJson('/api/manufacturados', $this->datos([
            ['articulo_insumo_id' => $harina->id, 'cantidad' => 2],   // 1 / 2 = 0 unidades
        ]))->assertCreated()->assertJsonPath('stock_posible', 0)->assertJsonPath('disponible', false);
    }

    public function test_baja_logica_restaura_y_protege_insumos_en_uso(): void
    {
        $this->comoRol(Rol::ADMIN);
        $harina = $this->insumo('Harina', 1000, 10);
        $id = $this->postJson('/api/manufacturados', $this->datos([
            ['articulo_insumo_id' => $harina->id, 'cantidad' => 1],
        ]))->assertCreated()->json('id');

        // El insumo está en una receta activa: no se puede dar de baja.
        $this->deleteJson("/api/insumos/{$harina->id}")->assertStatus(409)
            ->assertJsonFragment(['message' => 'El insumo se usa en recetas: Pizza muzzarella. Quítelo de ellas antes de darlo de baja.']);

        $this->deleteJson("/api/manufacturados/{$id}")->assertOk();
        $this->getJson('/api/manufacturados')->assertOk()->assertJsonCount(0);
        $this->getJson('/api/manufacturados/admin?baja=1')->assertOk()->assertJsonCount(1);
        $this->assertNotNull(ArticuloManufacturado::withTrashed()->find($id));

        // Con el producto dado de baja, el insumo ya puede darse de baja.
        $this->deleteJson("/api/insumos/{$harina->id}")->assertOk();

        $this->postJson("/api/manufacturados/{$id}/restaurar")->assertOk();
        $this->getJson('/api/manufacturados')->assertOk()->assertJsonCount(1)
            ->assertJsonPath('0.disponible', false); // su insumo está dado de baja
    }

    public function test_categoria_de_manufacturado_en_uso_no_se_elimina(): void
    {
        $this->comoRol(Rol::ADMIN);
        $harina = $this->insumo('Harina', 1000, 10);
        $this->postJson('/api/manufacturados', $this->datos([
            ['articulo_insumo_id' => $harina->id, 'cantidad' => 1],
        ]))->assertCreated();

        $this->deleteJson("/api/categorias-manufacturado/{$this->categoriaId}")->assertStatus(409);
        $nueva = $this->postJson('/api/categorias-manufacturado', ['denominacion' => 'Postres'])->assertCreated()->json('id');
        $this->postJson('/api/categorias-manufacturado', ['denominacion' => 'Postres'])->assertStatus(422);
        $this->deleteJson("/api/categorias-manufacturado/{$nueva}")->assertOk();
    }
}

<?php

namespace Tests\Feature;

use App\Models\ArticuloInsumo;
use App\Models\ArticuloManufacturado;
use App\Models\CategoriaArticulo;
use App\Models\SucursalInsumo;
use App\Models\UnidadMedida;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CarritoTest extends TestCase
{
    private int $sucursalId;

    protected function setUp(): void
    {
        parent::setUp();

        // Conexión exclusiva: nunca ejecutar migraciones sobre la base del .env.
        config([
            'database.default' => 'carrito_test',
            'database.connections.carrito_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('carrito_test');

        foreach ([
            '2025_10_10_120150_create_empresas_table',
            '2025_10_10_120631_create_sucursal_empresa_table',
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
    }

    private function insumo(string $nombre, float $precioVenta, int $stock, bool $elaborar): ArticuloInsumo
    {
        $insumo = ArticuloInsumo::create([
            'denominacion' => $nombre, 'precioCompra' => 100, 'precioVenta' => $precioVenta,
            'esParaElaborar' => $elaborar,
            'categoria_articulo_id' => CategoriaArticulo::firstOrCreate(['denominacion' => 'General'])->id,
            'unidad_medida_id' => UnidadMedida::firstOrCreate(['denominacion' => 'Unidad'])->id,
        ]);
        SucursalInsumo::create([
            'articulo_insumo_id' => $insumo->id, 'sucursal_empresa_id' => $this->sucursalId,
            'stockActual' => $stock, 'stockMinimo' => 1, 'stockMaximo' => 1000,
        ]);

        return $insumo;
    }

    private function pizza(ArticuloInsumo $ingrediente, float $cantidad): ArticuloManufacturado
    {
        $categoria = DB::table('categoria_articulo_manufacturado')->insertGetId(['denominacion' => 'Pizzas']);
        $pizza = ArticuloManufacturado::create([
            'denominacion' => 'Pizza', 'descripcion' => 'Pizza', 'precioVenta' => 5000, 'precioCosto' => 0,
            'tiempoEstimado' => 20, 'categoria_id' => $categoria,
        ]);
        $pizza->detalles()->create(['articulo_insumo_id' => $ingrediente->id, 'cantidad' => $cantidad]);

        return $pizza;
    }

    private function validar(array $items)
    {
        return $this->postJson('/api/carrito/validar', ['items' => $items]);
    }

    public function test_carrito_valido_devuelve_precios_vigentes_y_total(): void
    {
        $coca = $this->insumo('Coca-Cola', 1500, 20, false);
        $pizza = $this->pizza($this->insumo('Prepizza', 800, 10, true), 1);

        $this->validar([
            ['tipo' => 'insumo', 'id' => $coca->id, 'cantidad' => 2],
            ['tipo' => 'manufacturado', 'id' => $pizza->id, 'cantidad' => 3],
        ])->assertOk()
            ->assertJsonPath('puede_continuar', true)
            ->assertJsonPath('total', 18000)   // 2 * 1500 + 3 * 5000
            ->assertJsonPath('items.0.estado', 'ok')
            ->assertJsonPath('items.1.stock_max', 10);
    }

    public function test_stock_insuficiente_limita_la_cantidad(): void
    {
        $coca = $this->insumo('Coca-Cola', 1500, 3, false);

        $this->validar([['tipo' => 'insumo', 'id' => $coca->id, 'cantidad' => 5]])->assertOk()
            ->assertJsonPath('puede_continuar', false)
            ->assertJsonPath('items.0.estado', 'stock_insuficiente')
            ->assertJsonPath('items.0.cantidad_ok', 3)
            ->assertJsonPath('total', 4500);
    }

    public function test_sin_stock_y_manufacturado_sin_ingredientes(): void
    {
        $agotada = $this->insumo('Sprite', 1000, 0, false);
        $pizza = $this->pizza($this->insumo('Prepizza', 800, 1, true), 2);   // 1 / 2 = 0 porciones

        $this->validar([
            ['tipo' => 'insumo', 'id' => $agotada->id, 'cantidad' => 1],
            ['tipo' => 'manufacturado', 'id' => $pizza->id, 'cantidad' => 1],
        ])->assertOk()
            ->assertJsonPath('items.0.estado', 'sin_stock')
            ->assertJsonPath('items.1.estado', 'sin_stock')
            ->assertJsonPath('total', 0);
    }

    public function test_rechaza_insumos_de_elaboracion_bajas_e_inexistentes(): void
    {
        $harina = $this->insumo('Harina', 900, 50, true);       // solo sirve para elaborar
        $baja = $this->insumo('Vino', 5000, 5, false);
        $baja->delete();
        $pizza = $this->pizza($this->insumo('Prepizza', 800, 10, true), 1);
        $pizza->delete();

        $this->validar([
            ['tipo' => 'insumo', 'id' => $harina->id, 'cantidad' => 1],
            ['tipo' => 'insumo', 'id' => $baja->id, 'cantidad' => 1],
            ['tipo' => 'manufacturado', 'id' => $pizza->id, 'cantidad' => 1],
            ['tipo' => 'manufacturado', 'id' => 9999, 'cantidad' => 1],
        ])->assertOk()
            ->assertJsonPath('puede_continuar', false)
            ->assertJsonPath('items.0.estado', 'no_disponible')
            ->assertJsonPath('items.1.estado', 'no_disponible')
            ->assertJsonPath('items.2.estado', 'no_disponible')
            ->assertJsonPath('items.3.estado', 'no_disponible')
            ->assertJsonPath('total', 0);
    }

    public function test_valida_el_formato_del_carrito(): void
    {
        $this->validar([])->assertStatus(422);
        $this->validar([['tipo' => 'otro', 'id' => 1, 'cantidad' => 1]])->assertStatus(422);
        $this->validar([['tipo' => 'insumo', 'id' => 1, 'cantidad' => 0]])->assertStatus(422);
        $this->validar([['tipo' => 'insumo', 'id' => 1, 'cantidad' => 1000]])->assertStatus(422);
        $this->postJson('/api/carrito/validar', [])->assertStatus(422);
    }

    public function test_listado_publico_de_insumos_incluye_stock_y_categoria(): void
    {
        $this->insumo('Coca-Cola', 1500, 7, false);

        $this->getJson('/api/insumos?tipo=venta')->assertOk()
            ->assertJsonPath('0.stock_total', 7)
            ->assertJsonPath('0.categoria.denominacion', 'General');
    }
}

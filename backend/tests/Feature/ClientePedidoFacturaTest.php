<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\FacturaVenta;
use App\Models\PedidoVenta;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClientePedidoFacturaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Conexión exclusiva: nunca ejecutar migraciones sobre la base del .env.
        config([
            'database.default' => 'relaciones_test',
            'database.connections.relaciones_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('relaciones_test');

        // Solo las migraciones reales necesarias para este flujo.
        // No pretende validar toda la reconstrucción MySQL.
        foreach ([
            '2025_10_10_120150_create_empresas_table',
            '2025_10_10_120631_create_sucursal_empresa_table',
            '2025_10_10_123339_create_pais_table',
            '2025_10_10_123400_create_provincia_table',
            '2025_10_10_123517_create_localidad_table',
            '2025_10_10_123518_create_domicilio_table',
            '2025_10_10_235012_create_usuario_table',
            '2025_10_10_235020_create_cliente_table',
            '2025_10_10_235029_create_empleado_table',
            '2025_10_11_004706_create_pedido_venta_table',
            '2025_10_11_004707_create_factura_venta_table',
            '2026_09_13_005117_add_cliente_id_to_pedido_venta_table',
            '2026_09_20_000001_make_factura_pedido_unique',
        ] as $name) {
            (require database_path("migrations/{$name}.php"))->up();
        }

        DB::table('empresas')->insert(['id' => 1, 'nombre' => 'Prueba', 'razonSocial' => 'Prueba', 'cuil' => 20123456789]);
        DB::table('sucursal_empresa')->insert(['id' => 1, 'nombre' => 'Centro', 'horarioApertura' => '09:00', 'horarioCierre' => '23:00', 'empresa_id' => 1]);
        DB::table('pais')->insert(['id' => 1, 'nombre' => 'Argentina']);
        DB::table('provincia')->insert(['id' => 1, 'nombre' => 'Mendoza', 'pais_id' => 1]);
        DB::table('localidad')->insert(['id' => 1, 'nombre' => 'Capital', 'provincia_id' => 1]);
        DB::table('domicilio')->insert(['id' => 1, 'calle' => 'Prueba', 'numero' => 123, 'cp' => 5500, 'sucursalEmpresa_id' => 1, 'localidad_id' => 1]);
        DB::table('usuario')->insert([
            ['id' => 1, 'authOId' => 'cliente-prueba', 'username' => 'cliente'],
            ['id' => 2, 'authOId' => 'empleado-prueba', 'username' => 'empleado'],
            ['id' => 3, 'authOId' => 'otro-cliente', 'username' => 'otro'],
        ]);
        DB::table('empleado')->insert(['id' => 1, 'nombre' => 'Empleado', 'apellido' => 'Prueba', 'telefono' => 123456, 'email' => 'empleado@example.test', 'rol' => 'empleado', 'usuario_id' => 2]);
        foreach ([1 => 1, 2 => 3] as $id => $usuarioId) {
            Cliente::create(['nombre' => 'Cliente', 'apellido' => 'Prueba', 'telefono' => 123456, 'email' => "cliente{$id}@example.test", 'usuario_id' => $usuarioId, 'domicilio_id' => 1]);
        }
    }

    protected function tearDown(): void
    {
        DB::purge('relaciones_test');
        parent::tearDown();
    }

    private function crearPedido(?int $clienteId = 1): PedidoVenta
    {
        return PedidoVenta::create([
            'cliente_id' => $clienteId, 'empleado_id' => 1, 'sucursal_empresa_id' => 1,
            'horaEstimadaFinalizacion' => 30, 'subtotal' => 1000, 'descuento' => 0,
            'gastosEnvio' => 0, 'total' => 1000, 'totalCosto' => 500,
            'fechaPedido' => '2026-09-20', 'estado' => 'pendiente',
            'tipoEnvio' => 'takeAway', 'formaPago' => 'efectivo',
        ]);
    }

    private function crearFactura(PedidoVenta $pedido): FacturaVenta
    {
        return $pedido->factura()->create([
            'fechaFacturacion' => '2026-09-20', 'numeroComprobante' => 1,
            'formaPago' => 'efectivo', 'subtotal' => 1000, 'descuento' => 0,
            'gastosEnvio' => 0, 'totalVenta' => 1000,
        ]);
    }

    public function test_recupera_pedidos_y_facturas_del_cliente_correcto(): void
    {
        $pedido = $this->crearPedido();
        $sinFactura = $this->crearPedido();
        $otroPedido = $this->crearPedido(2);
        $factura = $this->crearFactura($pedido);
        $this->crearFactura($otroPedido);

        $cliente = Cliente::with('pedidos.factura')->findOrFail(1);
        $this->assertEqualsCanonicalizing([$pedido->id, $sinFactura->id], $cliente->pedidos->modelKeys());
        $this->assertTrue($cliente->pedidos->find($pedido->id)->factura->is($factura));
        $this->assertNull($cliente->pedidos->find($sinFactura->id)->factura);
        $this->assertTrue($factura->fresh()->pedidoVenta->cliente->is($cliente));
        $this->assertTrue($pedido->fresh()->cliente->is($cliente));
    }

    public function test_conserva_pedidos_anteriores_sin_cliente(): void
    {
        $pedido = $this->crearPedido(null);
        $this->assertNull($pedido->fresh()->cliente);
        $this->assertCount(0, Cliente::findOrFail(1)->pedidos);
    }

    public function test_rechaza_una_segunda_factura_del_mismo_pedido(): void
    {
        $pedido = $this->crearPedido();
        $this->crearFactura($pedido);
        $this->expectException(QueryException::class);
        $this->crearFactura($pedido);
    }

    public function test_migracion_detecta_duplicados_sin_borrar_facturas(): void
    {
        $migration = require database_path('migrations/2026_09_20_000001_make_factura_pedido_unique.php');
        $migration->down();
        $pedido = $this->crearPedido();
        $this->crearFactura($pedido);
        $this->crearFactura($pedido);

        try {
            $migration->up();
            $this->fail('La migración debía rechazar las facturas duplicadas.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Hay pedidos con varias facturas', $exception->getMessage());
            $this->assertSame(2, FacturaVenta::count());
        }
    }
}

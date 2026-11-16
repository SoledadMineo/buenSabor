<?php

namespace Database\Seeders;

use App\Models\ArticuloInsumo;
use App\Models\CategoriaArticulo;
use App\Models\SucursalEmpresa;
use App\Models\SucursalInsumo;
use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 40 insumos para elaborar + 15 insumos de venta directa, con unidad, categoría
 * y stock en la sucursal. Se puede ejecutar varias veces: no duplica registros.
 */
class InsumoSeeder extends Seeder
{
    // [denominacion, categoria, unidad, precioCompra, stockActual, stockMinimo, stockMaximo]
    private const ELABORACION = [
        ['Harina 000', 'Almacén', 'Kilogramo', 900, 80, 20, 150],
        ['Harina integral', 'Almacén', 'Kilogramo', 1200, 30, 10, 60],
        ['Azúcar', 'Almacén', 'Kilogramo', 1000, 50, 15, 100],
        ['Sal fina', 'Almacén', 'Kilogramo', 600, 25, 5, 50],
        ['Aceite de girasol', 'Almacén', 'Litro', 2200, 40, 10, 80],
        ['Aceite de oliva', 'Almacén', 'Litro', 7500, 12, 5, 30],
        ['Levadura fresca', 'Almacén', 'Kilogramo', 3500, 6, 2, 12],
        ['Salsa de tomate', 'Almacén', 'Litro', 1800, 35, 10, 70],
        ['Pan de hamburguesa', 'Panificados', 'Unidad', 250, 200, 60, 400],
        ['Pan de pancho', 'Panificados', 'Unidad', 200, 150, 50, 300],
        ['Tapas de empanada', 'Panificados', 'Unidad', 120, 300, 100, 600],
        ['Prepizza', 'Panificados', 'Unidad', 800, 60, 20, 120],
        ['Carne picada', 'Carnes', 'Kilogramo', 8500, 40, 15, 80],
        ['Bife de chorizo', 'Carnes', 'Kilogramo', 14000, 20, 8, 40],
        ['Pechuga de pollo', 'Carnes', 'Kilogramo', 6500, 30, 10, 60],
        ['Panceta ahumada', 'Carnes', 'Kilogramo', 9000, 10, 4, 20],
        ['Jamón cocido', 'Fiambres', 'Kilogramo', 9500, 15, 5, 30],
        ['Salchichas', 'Fiambres', 'Unidad', 450, 180, 60, 360],
        ['Muzzarella', 'Lácteos', 'Kilogramo', 7800, 45, 15, 90],
        ['Queso cheddar', 'Lácteos', 'Kilogramo', 9800, 12, 5, 25],
        ['Queso parmesano', 'Lácteos', 'Kilogramo', 14000, 6, 2, 12],
        ['Crema de leche', 'Lácteos', 'Litro', 4200, 18, 6, 36],
        ['Manteca', 'Lácteos', 'Kilogramo', 8000, 14, 5, 28],
        ['Leche entera', 'Lácteos', 'Litro', 1100, 40, 12, 80],
        ['Huevos', 'Lácteos', 'Unidad', 180, 360, 120, 720],
        ['Tomate', 'Verduras', 'Kilogramo', 1500, 30, 10, 60],
        ['Lechuga', 'Verduras', 'Kilogramo', 1800, 12, 5, 24],
        ['Cebolla', 'Verduras', 'Kilogramo', 800, 40, 12, 80],
        ['Morrón rojo', 'Verduras', 'Kilogramo', 2800, 10, 4, 20],
        ['Papa', 'Verduras', 'Kilogramo', 700, 100, 30, 200],
        ['Champiñones', 'Verduras', 'Kilogramo', 5200, 6, 3, 12],
        ['Rúcula', 'Verduras', 'Kilogramo', 3200, 4, 2, 8],
        ['Ajo', 'Condimentos', 'Kilogramo', 6000, 3, 1, 6],
        ['Orégano', 'Condimentos', 'Kilogramo', 9000, 2, 1, 4],
        ['Pimienta negra', 'Condimentos', 'Kilogramo', 18000, 1, 1, 3],
        ['Pimentón', 'Condimentos', 'Kilogramo', 11000, 2, 1, 4],
        ['Mayonesa', 'Salsas', 'Kilogramo', 3200, 20, 6, 40],
        ['Ketchup', 'Salsas', 'Kilogramo', 2900, 18, 6, 36],
        ['Mostaza', 'Salsas', 'Kilogramo', 2700, 14, 5, 28],
        ['Chocolate cobertura', 'Dulces', 'Kilogramo', 9500, 8, 3, 16],
    ];

    private const VENTA = [
        ['Coca-Cola 500 ml', 'Bebidas sin alcohol', 'Unidad', 900, 120, 40, 240],
        ['Coca-Cola 1,5 L', 'Bebidas sin alcohol', 'Unidad', 1800, 60, 20, 120],
        ['Sprite 500 ml', 'Bebidas sin alcohol', 'Unidad', 880, 90, 30, 180],
        ['Fanta 500 ml', 'Bebidas sin alcohol', 'Unidad', 880, 90, 30, 180],
        ['Agua mineral 500 ml', 'Bebidas sin alcohol', 'Unidad', 600, 150, 50, 300],
        ['Agua con gas 500 ml', 'Bebidas sin alcohol', 'Unidad', 620, 100, 30, 200],
        ['Jugo de naranja 500 ml', 'Bebidas sin alcohol', 'Unidad', 1100, 50, 20, 100],
        ['Limonada 500 ml', 'Bebidas sin alcohol', 'Unidad', 1000, 40, 15, 80],
        ['Cerveza rubia 473 ml', 'Bebidas con alcohol', 'Unidad', 1500, 80, 30, 160],
        ['Cerveza negra 473 ml', 'Bebidas con alcohol', 'Unidad', 1700, 40, 15, 80],
        ['Cerveza IPA 473 ml', 'Bebidas con alcohol', 'Unidad', 2000, 8, 15, 80],
        ['Vino tinto 750 ml', 'Bebidas con alcohol', 'Unidad', 5500, 24, 8, 48],
        ['Vino blanco 750 ml', 'Bebidas con alcohol', 'Unidad', 5200, 18, 8, 36],
        ['Alfajor', 'Postres', 'Unidad', 700, 70, 25, 140],
        ['Flan casero', 'Postres', 'Unidad', 1200, 4, 10, 40],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $sucursal = SucursalEmpresa::firstOrFail();

            foreach ([[self::ELABORACION, true], [self::VENTA, false]] as [$lista, $paraElaborar]) {
                foreach ($lista as [$nombre, $categoria, $unidad, $compra, $actual, $minimo, $maximo]) {
                    $insumo = ArticuloInsumo::withTrashed()->firstOrCreate(
                        ['denominacion' => $nombre],
                        [
                            'precioCompra' => $compra,
                            // Venta directa: margen del 80 %. Elaboración: referencia del 50 %.
                            'precioVenta' => round($compra * ($paraElaborar ? 1.5 : 1.8)),
                            'esParaElaborar' => $paraElaborar,
                            'categoria_articulo_id' => CategoriaArticulo::firstOrCreate(['denominacion' => $categoria])->id,
                            'unidad_medida_id' => UnidadMedida::firstOrCreate(['denominacion' => $unidad])->id,
                        ]
                    );

                    SucursalInsumo::firstOrCreate(
                        ['sucursal_empresa_id' => $sucursal->id, 'articulo_insumo_id' => $insumo->id],
                        ['stockActual' => $actual, 'stockMinimo' => $minimo, 'stockMaximo' => $maximo]
                    );
                }
            }
        });
    }
}

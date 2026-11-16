<?php

namespace Database\Seeders;

use App\Models\ArticuloInsumo;
use App\Models\ArticuloManufacturado;
use App\Models\CategoriaArticuloManufacturado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 15 productos manufacturados con receta, usando los insumos de InsumoSeeder.
 * Se puede ejecutar varias veces: no duplica productos ni recetas.
 */
class ManufacturadoSeeder extends Seeder
{
    // [denominacion, descripcion, categoria, minutos, [insumo => cantidad]]
    // La cantidad está en la unidad del insumo (kg, litros o unidades).
    private const PRODUCTOS = [
        ['Pizza muzzarella', 'Pizza con salsa de tomate, muzzarella y orégano.', 'Pizzas', 20,
            ['Prepizza' => 1, 'Salsa de tomate' => 0.1, 'Muzzarella' => 0.25, 'Orégano' => 0.002]],
        ['Pizza especial', 'Pizza con muzzarella, jamón cocido y morrón.', 'Pizzas', 25,
            ['Prepizza' => 1, 'Salsa de tomate' => 0.1, 'Muzzarella' => 0.25, 'Jamón cocido' => 0.1, 'Morrón rojo' => 0.05]],
        ['Pizza napolitana', 'Pizza con muzzarella, rodajas de tomate y ajo.', 'Pizzas', 25,
            ['Prepizza' => 1, 'Salsa de tomate' => 0.1, 'Muzzarella' => 0.25, 'Tomate' => 0.15, 'Ajo' => 0.005]],
        ['Pizza fugazzeta', 'Pizza con abundante cebolla y muzzarella.', 'Pizzas', 25,
            ['Prepizza' => 1, 'Muzzarella' => 0.3, 'Cebolla' => 0.25, 'Orégano' => 0.002]],
        ['Hamburguesa clásica', 'Carne, lechuga, tomate y mayonesa.', 'Hamburguesas', 15,
            ['Pan de hamburguesa' => 1, 'Carne picada' => 0.15, 'Lechuga' => 0.02, 'Tomate' => 0.03, 'Mayonesa' => 0.02]],
        ['Hamburguesa completa', 'Carne, cheddar, panceta, huevo, lechuga y tomate.', 'Hamburguesas', 20,
            ['Pan de hamburguesa' => 1, 'Carne picada' => 0.15, 'Queso cheddar' => 0.04, 'Panceta ahumada' => 0.03,
                'Huevos' => 1, 'Lechuga' => 0.02, 'Tomate' => 0.03, 'Ketchup' => 0.02]],
        ['Hamburguesa de pollo', 'Pechuga de pollo con cheddar, lechuga y mayonesa.', 'Hamburguesas', 18,
            ['Pan de hamburguesa' => 1, 'Pechuga de pollo' => 0.15, 'Lechuga' => 0.02, 'Mayonesa' => 0.02, 'Queso cheddar' => 0.03]],
        ['Papas fritas', 'Porción de papas fritas.', 'Acompañamientos', 12,
            ['Papa' => 0.3, 'Aceite de girasol' => 0.1, 'Sal fina' => 0.005]],
        ['Empanada de carne', 'Empanada de carne cortada a cuchillo.', 'Empanadas', 10,
            ['Tapas de empanada' => 1, 'Carne picada' => 0.05, 'Cebolla' => 0.04, 'Pimentón' => 0.001]],
        ['Empanada de jamón y queso', 'Empanada de jamón cocido y muzzarella.', 'Empanadas', 10,
            ['Tapas de empanada' => 1, 'Jamón cocido' => 0.03, 'Muzzarella' => 0.03]],
        ['Pancho clásico', 'Pancho con ketchup, mostaza y mayonesa.', 'Panchos', 8,
            ['Pan de pancho' => 1, 'Salchichas' => 1, 'Ketchup' => 0.01, 'Mostaza' => 0.01, 'Mayonesa' => 0.01]],
        ['Ensalada mixta', 'Lechuga, tomate y cebolla.', 'Ensaladas', 8,
            ['Lechuga' => 0.1, 'Tomate' => 0.1, 'Cebolla' => 0.03, 'Aceite de oliva' => 0.01, 'Sal fina' => 0.002]],
        ['Ensalada de rúcula y parmesano', 'Rúcula, tomate y lascas de parmesano.', 'Ensaladas', 8,
            ['Rúcula' => 0.08, 'Queso parmesano' => 0.03, 'Tomate' => 0.08, 'Aceite de oliva' => 0.01]],
        ['Milanesa de pollo con papas', 'Milanesa de pechuga con papas fritas.', 'Platos', 25,
            ['Pechuga de pollo' => 0.2, 'Huevos' => 1, 'Harina 000' => 0.05, 'Papa' => 0.25, 'Aceite de girasol' => 0.12]],
        ['Brownie con chocolate', 'Brownie de chocolate casero.', 'Postres', 30,
            ['Chocolate cobertura' => 0.08, 'Harina 000' => 0.06, 'Azúcar' => 0.07, 'Manteca' => 0.05, 'Huevos' => 2]],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            foreach (self::PRODUCTOS as [$nombre, $descripcion, $categoria, $minutos, $receta]) {
                $producto = ArticuloManufacturado::withTrashed()->firstOrCreate(
                    ['denominacion' => $nombre],
                    [
                        'descripcion' => $descripcion,
                        'precioVenta' => 0,
                        'precioCosto' => 0,
                        'tiempoEstimado' => $minutos,
                        'categoria_id' => CategoriaArticuloManufacturado::firstOrCreate(['denominacion' => $categoria])->id,
                    ]
                );

                if ($producto->detalles()->exists()) {
                    continue; // ya cargado
                }

                foreach ($receta as $insumo => $cantidad) {
                    $producto->detalles()->create([
                        'articulo_insumo_id' => ArticuloInsumo::where('denominacion', $insumo)->firstOrFail()->id,
                        'cantidad' => $cantidad,
                    ]);
                }

                $producto->actualizarCosto();
                // Precio de venta: costo más 150 %, redondeado a múltiplos de $50.
                $producto->update(['precioVenta' => max(50, round($producto->precioCosto * 2.5 / 50) * 50)]);
            }
        });
    }
}

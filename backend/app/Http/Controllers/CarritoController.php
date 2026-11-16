<?php

namespace App\Http\Controllers;

use App\Models\ArticuloInsumo;
use App\Models\ArticuloManufacturado;
use App\Models\SucursalEmpresa;
use App\Models\SucursalInsumo;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    /**
     * Revisa el carrito contra los datos actuales: precio vigente, si el producto
     * sigue a la venta y cuántas unidades hay en stock. No modifica nada.
     *
     * Cada línea vuelve con un estado:
     *  - ok: se puede comprar la cantidad pedida.
     *  - stock_insuficiente: hay stock, pero menos que lo pedido (cantidad_ok = máximo posible).
     *  - sin_stock: no hay unidades.
     *  - no_disponible: no existe, está dado de baja o no se vende directamente.
     */
    public function validar(Request $request)
    {
        $datos = $request->validate([
            'items' => 'required|array|min:1|max:50',
            'items.*.tipo' => 'required|in:insumo,manufacturado',
            'items.*.id' => 'required|integer',
            'items.*.cantidad' => 'required|integer|min:1|max:999',
        ]);

        $sucursalId = SucursalEmpresa::min('id');
        $total = 0;

        $lineas = collect($datos['items'])->map(function ($item) use ($sucursalId, &$total) {
            $linea = $this->evaluar($item['tipo'], (int) $item['id'], (int) $item['cantidad'], $sucursalId);
            $total += $linea['precioVenta'] * $linea['cantidad_ok'];

            return $linea;
        })->values();

        return [
            'items' => $lineas,
            'total' => round($total, 2),
            'puede_continuar' => $lineas->every(fn ($l) => $l['estado'] === 'ok'),
        ];
    }

    private function evaluar(string $tipo, int $id, int $cantidad, ?int $sucursalId): array
    {
        $base = ['tipo' => $tipo, 'id' => $id, 'cantidad' => $cantidad];

        $producto = $tipo === 'insumo'
            ? ArticuloInsumo::where('esParaElaborar', false)->find($id)   // solo los de venta directa
            : ArticuloManufacturado::with('detalles.articuloInsumo')->find($id);

        if (!$producto) {
            return $base + [
                'denominacion' => null, 'precioVenta' => 0, 'stock_max' => 0,
                'cantidad_ok' => 0, 'estado' => 'no_disponible',
            ];
        }

        $stock = $tipo === 'insumo'
            ? (int) SucursalInsumo::where('articulo_insumo_id', $id)->where('sucursal_empresa_id', $sucursalId)->value('stockActual')
            : $producto->stockCalculado($sucursalId);

        $estado = $stock <= 0 ? 'sin_stock' : ($stock < $cantidad ? 'stock_insuficiente' : 'ok');

        return $base + [
            'denominacion' => $producto->denominacion,
            'precioVenta' => (float) $producto->precioVenta,
            'stock_max' => $stock,
            'cantidad_ok' => min($cantidad, max($stock, 0)),
            'estado' => $estado,
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ArticuloInsumo;
use App\Models\SucursalInsumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function sucursales()
    {
        return \App\Models\SucursalEmpresa::orderBy('nombre')->get(['id', 'nombre']);
    }

    // Stock de un insumo en todas las sucursales.
    public function porInsumo($id)
    {
        $insumo = ArticuloInsumo::findOrFail($id);

        return $insumo->sucursales()->with('sucursalEmpresa:id,nombre')->get();
    }

    // Define stock actual, mínimo y máximo de un insumo en una sucursal (alta o reemplazo).
    public function definir(Request $request, $id)
    {
        $insumo = ArticuloInsumo::findOrFail($id);

        $datos = $request->validate([
            'sucursal_empresa_id' => 'required|integer|exists:sucursal_empresa,id',
            'stockActual' => 'required|integer|min:0',
            'stockMinimo' => 'required|integer|min:0',
            'stockMaximo' => 'required|integer|gte:stockMinimo',
        ]);

        $stock = SucursalInsumo::updateOrCreate(
            ['articulo_insumo_id' => $insumo->id, 'sucursal_empresa_id' => $datos['sucursal_empresa_id']],
            $datos
        );

        return response()->json($stock, $stock->wasRecentlyCreated ? 201 : 200);
    }

    // Suma (compra, devolución) o resta (merma) unidades al stock existente.
    public function ajustar(Request $request, $id)
    {
        $datos = $request->validate([
            'sucursal_empresa_id' => 'required|integer|exists:sucursal_empresa,id',
            'cantidad' => 'required|integer|not_in:0',
        ]);

        return DB::transaction(function () use ($id, $datos) {
            $stock = SucursalInsumo::where('articulo_insumo_id', $id)
                ->where('sucursal_empresa_id', $datos['sucursal_empresa_id'])
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                return response()->json(['message' => 'El insumo no tiene stock definido en esa sucursal.'], 404);
            }

            $nuevo = $stock->stockActual + $datos['cantidad'];
            if ($nuevo < 0) {
                return response()->json(['message' => 'El stock no puede quedar negativo.'], 422);
            }
            if ($nuevo > $stock->stockMaximo) {
                return response()->json(['message' => "Supera el stock máximo ({$stock->stockMaximo})."], 422);
            }

            $stock->update(['stockActual' => $nuevo]);

            return $stock;
        });
    }

    // Insumos con stock por debajo del mínimo.
    public function alertas()
    {
        return SucursalInsumo::with(['articuloInsumo:id,denominacion,esParaElaborar', 'sucursalEmpresa:id,nombre'])
            ->whereColumn('stockActual', '<', 'stockMinimo')
            ->get();
    }
}

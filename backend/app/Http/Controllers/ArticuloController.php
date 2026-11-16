<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArticuloInsumo;
use App\Models\ArticuloManufacturado;
use App\Models\ImagenInsumo;

class ArticuloController extends Controller
{
    // Listado público. Filtros opcionales: ?tipo=elaboracion|venta, ?buscar=, ?categoria=
    public function insumos(Request $request)
    {
        return $this->consultaInsumos($request)
            ->with(['imagen', 'categoria'])
            ->withSum('sucursales as stock_total', 'stockActual')
            ->get();
    }

    private function consultaInsumos(Request $request)
    {
        return ArticuloInsumo::query()
            ->deTipo($request->query('tipo'))
            ->when($request->query('buscar'), fn ($q, $b) => $q->where('denominacion', 'like', "%{$b}%"))
            ->when($request->query('categoria'), fn ($q, $c) => $q->where('categoria_articulo_id', $c))
            ->orderBy('denominacion');
    }

    public function insumoById($id)
    {
        return ArticuloInsumo::findOrFail($id);
    }


    public function todos()
    {
        $insumos = ArticuloInsumo::all();
        $manufacturados = ArticuloManufacturado::all();

        return [
            "insumos" => $insumos,
            "manufacturados" => $manufacturados
        ];
    }

    // Grilla administrativa. ?baja=1 muestra los insumos dados de baja.
    public function adminIndex(Request $request)
    {
        $insumos = $this->consultaInsumos($request)
            ->with(['imagen', 'categoria', 'unidadMedida'])
            ->withSum('sucursales as stock_total', 'stockActual')
            ->when($request->boolean('baja'), fn ($q) => $q->onlyTrashed())
            ->get()
            ->map(function ($i) {
                $i->tipo = 'insumo';
                $i->stock_bajo = $i->sucursales()->whereColumn('stockActual', '<', 'stockMinimo')->exists();
                return $i;
            });

        $manufacturados = $request->query('tipo')
            ? collect()
            : ArticuloManufacturado::with(['categoria', 'detalles.articuloInsumo'])
                ->when($request->query('buscar'), fn ($q, $b) => $q->where('denominacion', 'like', "%{$b}%"))
                ->when($request->boolean('baja'), fn ($q) => $q->onlyTrashed())
                ->orderBy('denominacion')
                ->get()
                ->map(function ($m) {
                    $m->tipo = 'manufacturado';
                    $m->stock_posible = $m->stockCalculado();
                    return $m;
                });

        return $insumos->concat($manufacturados)->values();
    }

    public function storeInsumo(Request $request)
    {
        $validated = $request->validate([
            'denominacion' => 'required|string|max:255',
            'precioCompra' => 'required|numeric|min:0',
            'precioVenta' => 'required|numeric|min:0',
            'esParaElaborar' => 'required|boolean',
            'categoria_articulo_id' => 'required|integer|exists:categoria_articulo,id',
            'unidad_medida_id' => 'required|integer|exists:unidad_medida,id',
            'imagen' => 'nullable|image|max:2048',
        ]);

        $imagenId = null;

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');

            $path = $file->store('insumos', 'public'); // ej: insumos/harina.jpg

            $imagen = ImagenInsumo::create([
                'denominacion' => $path,
            ]);

            $imagenId = $imagen->id;
        }

        $insumo = ArticuloInsumo::create([
            'denominacion' => $validated['denominacion'],
            'precioCompra' => $validated['precioCompra'],
            'precioVenta' => $validated['precioVenta'],
            'esParaElaborar' => $validated['esParaElaborar'],
            'categoria_articulo_id' => $validated['categoria_articulo_id'],
            'unidad_medida_id' => $validated['unidad_medida_id'],
            'imagen_insumo_id' => $imagenId,
        ]);

        return response()->json($insumo->load('imagen'), 201);
    }

    public function updateInsumo(Request $request, $id)
    {
        $insumo = ArticuloInsumo::findOrFail($id);

        $validated = $request->validate([
            'denominacion' => 'required|string|max:255',
            'precioCompra' => 'required|numeric|min:0',
            'precioVenta' => 'required|numeric|min:0',
            'esParaElaborar' => 'required|boolean',
            'categoria_articulo_id' => 'required|integer|exists:categoria_articulo,id',
            'unidad_medida_id' => 'required|integer|exists:unidad_medida,id',
            'imagen' => 'nullable|image|max:2048',
        ]);

        // No permitir pasar a "venta" un insumo que ya forma parte de una receta.
        if (!$validated['esParaElaborar'] && $insumo->esParaElaborar
            && $insumo->articulosManufacturadosDetalle()->exists()) {
            return response()->json([
                'message' => 'El insumo se usa en recetas: no puede pasar a venta directa.',
            ], 422);
        }

        unset($validated['imagen']);
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('insumos', 'public');
            $validated['imagen_insumo_id'] = ImagenInsumo::create(['denominacion' => $path])->id;
        }

        $insumo->update($validated);

        // El costo de los productos que lo usan depende de su precio de compra.
        ArticuloManufacturado::whereIn('id', $insumo->articulosManufacturadosDetalle()->select('articulo_manufacturado_id'))
            ->get()->each->actualizarCosto();

        return $insumo->load('imagen', 'categoria', 'unidadMedida');
    }

    // Baja lógica (SoftDeletes): conserva recetas, pedidos y facturas.
    public function destroyInsumo($id)
    {
        $insumo = ArticuloInsumo::findOrFail($id);

        $usadoEn = $insumo->articulosManufacturadosDetalle()
            ->whereHas('articuloManufacturado')
            ->with('articuloManufacturado:id,denominacion')->get()
            ->pluck('articuloManufacturado.denominacion')->unique()->values();
        if ($usadoEn->isNotEmpty()) {
            return response()->json([
                'message' => 'El insumo se usa en recetas: ' . $usadoEn->implode(', ') . '. Quítelo de ellas antes de darlo de baja.',
            ], 409);
        }

        $insumo->delete();

        return response()->json(['message' => 'Insumo dado de baja.']);
    }

    public function restoreInsumo($id)
    {
        $insumo = ArticuloInsumo::onlyTrashed()->findOrFail($id);
        $insumo->restore();

        return $insumo;
    }
}

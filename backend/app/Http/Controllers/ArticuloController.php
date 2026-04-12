<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArticuloInsumo;
use App\Models\ArticuloManufacturado;
use App\Models\ImagenInsumo;

class ArticuloController extends Controller
{
    public function insumos()
    {
        return ArticuloInsumo::with('imagen')->get();
    }

    public function manufacturados()
    {
        return ArticuloManufacturado::all();
    }

    public function insumoById($id)
    {
        return ArticuloInsumo::findOrFail($id);
    }

    public function manufacturadoById($id)
    {
        return ArticuloManufacturado::findOrFail($id);
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

    public function adminIndex()
    {
        $insumos = ArticuloInsumo::with('imagen')->get()->map(function ($i) {
            $i->tipo = 'insumo';
            return $i;
        });


        $manufacturados = ArticuloManufacturado::all()->map(function ($m) {
            $m->tipo = 'manufacturado';
            return $m;
        });

        return $insumos->concat($manufacturados)->values();
    }

    public function storeInsumo(Request $request)
    {
        $validated = $request->validate([
            'denominacion' => 'required|string',
            'precioCompra' => 'required|numeric',
            'precioVenta' => 'required|numeric',
            'esParaElaborar' => 'required|boolean',
            'categoria_articulo_id' => 'required|integer',
            'unidad_medida_id' => 'required|integer',
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
}

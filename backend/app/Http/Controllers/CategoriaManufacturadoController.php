<?php

namespace App\Http\Controllers;

use App\Models\CategoriaArticuloManufacturado;
use Illuminate\Http\Request;

class CategoriaManufacturadoController extends Controller
{
    public function index()
    {
        return CategoriaArticuloManufacturado::orderBy('denominacion')->get();
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'denominacion' => 'required|string|max:255|unique:categoria_articulo_manufacturado,denominacion',
        ]);

        return response()->json(CategoriaArticuloManufacturado::create($datos), 201);
    }

    public function update(Request $request, $id)
    {
        $categoria = CategoriaArticuloManufacturado::findOrFail($id);
        $datos = $request->validate([
            'denominacion' => "required|string|max:255|unique:categoria_articulo_manufacturado,denominacion,{$categoria->id}",
        ]);
        $categoria->update($datos);

        return $categoria;
    }

    public function destroy($id)
    {
        $categoria = CategoriaArticuloManufacturado::findOrFail($id);

        // Cuenta también los productos dados de baja, que siguen referenciando la categoría.
        if ($categoria->articuloManufacturado()->withTrashed()->exists()) {
            return response()->json([
                'message' => 'La categoría está en uso por productos y no puede eliminarse.',
            ], 409);
        }

        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada.']);
    }
}

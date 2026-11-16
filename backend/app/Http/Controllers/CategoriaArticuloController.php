<?php

namespace App\Http\Controllers;

use App\Models\CategoriaArticulo;
use Illuminate\Http\Request;

class CategoriaArticuloController extends Controller
{
    public function categorias()
    {
        return CategoriaArticulo::orderBy('denominacion')->get();
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'denominacion' => 'required|string|max:255|unique:categoria_articulo,denominacion',
        ]);

        return response()->json(CategoriaArticulo::create($datos), 201);
    }

    public function update(Request $request, $id)
    {
        $categoria = CategoriaArticulo::findOrFail($id);
        $datos = $request->validate([
            'denominacion' => "required|string|max:255|unique:categoria_articulo,denominacion,{$categoria->id}",
        ]);
        $categoria->update($datos);

        return $categoria;
    }

    public function destroy($id)
    {
        $categoria = CategoriaArticulo::findOrFail($id);

        // Cuenta también los insumos dados de baja, que siguen referenciando la categoría.
        if ($categoria->articuloInsumos()->withTrashed()->exists()) {
            return response()->json([
                'message' => 'La categoría está en uso por insumos y no puede eliminarse.',
            ], 409);
        }

        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada.']);
    }
}

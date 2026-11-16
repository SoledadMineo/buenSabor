<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    public function medidas()
    {
        return UnidadMedida::orderBy('denominacion')->get();
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'denominacion' => 'required|string|max:255|unique:unidad_medida,denominacion',
        ]);

        return response()->json(UnidadMedida::create($datos), 201);
    }

    public function update(Request $request, $id)
    {
        $unidad = UnidadMedida::findOrFail($id);
        $datos = $request->validate([
            'denominacion' => "required|string|max:255|unique:unidad_medida,denominacion,{$unidad->id}",
        ]);
        $unidad->update($datos);

        return $unidad;
    }

    public function destroy($id)
    {
        $unidad = UnidadMedida::findOrFail($id);

        if ($unidad->articuloInsumos()->withTrashed()->exists()) {
            return response()->json([
                'message' => 'La unidad de medida está en uso por insumos y no puede eliminarse.',
            ], 409);
        }

        $unidad->delete();

        return response()->json(['message' => 'Unidad de medida eliminada.']);
    }
}

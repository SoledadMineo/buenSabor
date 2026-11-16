<?php

namespace App\Http\Controllers;

use App\Models\ArticuloManufacturado;
use App\Models\ImagenManufacturado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ManufacturadoController extends Controller
{
    private const RELACIONES = ['categoria', 'imagenes', 'detalles.articuloInsumo.unidadMedida'];

    // Listado público. Filtros: ?buscar=, ?categoria=. Incluye costo y disponibilidad.
    public function index(Request $request)
    {
        return ArticuloManufacturado::query()
            ->with(self::RELACIONES)
            ->when($request->query('buscar'), fn ($q, $b) => $q->where('denominacion', 'like', "%{$b}%"))
            ->when($request->query('categoria'), fn ($q, $c) => $q->where('categoria_id', $c))
            ->orderBy('denominacion')
            ->get()
            ->map(fn ($m) => $this->conCalculos($m));
    }

    public function show($id)
    {
        return $this->conCalculos(ArticuloManufacturado::with(self::RELACIONES)->findOrFail($id));
    }

    // Grilla administrativa de manufacturados (?baja=1 muestra los dados de baja).
    public function admin(Request $request)
    {
        return ArticuloManufacturado::query()
            ->with(self::RELACIONES)
            ->when($request->boolean('baja'), fn ($q) => $q->onlyTrashed())
            ->orderBy('denominacion')
            ->get()
            ->map(fn ($m) => $this->conCalculos($m));
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas());

        $manufacturado = DB::transaction(function () use ($request, $datos) {
            $m = ArticuloManufacturado::create($datos + ['precioCosto' => 0]);
            $this->guardarReceta($m, $datos['ingredientes']);
            $this->guardarImagen($request, $m);
            $m->actualizarCosto();

            return $m;
        });

        return response()->json($this->conCalculos($manufacturado->load(self::RELACIONES)), 201);
    }

    public function update(Request $request, $id)
    {
        $manufacturado = ArticuloManufacturado::findOrFail($id);
        $datos = $request->validate($this->reglas());

        DB::transaction(function () use ($request, $datos, $manufacturado) {
            $manufacturado->update($datos);
            $manufacturado->detalles()->delete();
            $this->guardarReceta($manufacturado, $datos['ingredientes']);
            $this->guardarImagen($request, $manufacturado);
            $manufacturado->actualizarCosto();
        });

        return $this->conCalculos($manufacturado->fresh(self::RELACIONES));
    }

    // Baja lógica: conserva pedidos, facturas y promociones.
    public function destroy($id)
    {
        ArticuloManufacturado::findOrFail($id)->delete();

        return response()->json(['message' => 'Producto dado de baja.']);
    }

    public function restore($id)
    {
        $manufacturado = ArticuloManufacturado::onlyTrashed()->findOrFail($id);
        $manufacturado->restore();

        return $this->conCalculos($manufacturado->load(self::RELACIONES));
    }

    private function reglas(): array
    {
        return [
            'denominacion' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'precioVenta' => 'required|numeric|min:0',
            'tiempoEstimado' => 'required|integer|min:1',
            'categoria_id' => 'required|integer|exists:categoria_articulo_manufacturado,id',
            'imagen' => 'nullable|image|max:2048',
            'ingredientes' => 'required|array|min:1',
            // Solo insumos para elaborar y que no estén dados de baja; sin repetir.
            'ingredientes.*.articulo_insumo_id' => [
                'required', 'integer', 'distinct',
                Rule::exists('articulo_insumo', 'id')
                    ->where(fn ($q) => $q->where('esParaElaborar', true)->whereNull('deleted_at')),
            ],
            'ingredientes.*.cantidad' => 'required|numeric|gt:0',
        ];
    }

    private function guardarReceta(ArticuloManufacturado $m, array $ingredientes): void
    {
        foreach ($ingredientes as $i) {
            $m->detalles()->create([
                'articulo_insumo_id' => $i['articulo_insumo_id'],
                'cantidad' => $i['cantidad'],
            ]);
        }
    }

    // La imagen nueva reemplaza a las anteriores.
    private function guardarImagen(Request $request, ArticuloManufacturado $m): void
    {
        if (!$request->hasFile('imagen')) {
            return;
        }
        $m->imagenes()->delete();
        $path = $request->file('imagen')->store('manufacturados', 'public');
        ImagenManufacturado::create(['denominacion' => $path, 'articulo_manufacturado_id' => $m->id]);
    }

    private function conCalculos(ArticuloManufacturado $m): ArticuloManufacturado
    {
        $m->costo_calculado = $m->precioCostoCalculado();
        $m->stock_posible = $m->stockCalculado();
        $m->disponible = $m->stock_posible > 0;

        return $m;
    }
}

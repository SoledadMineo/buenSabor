<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticuloManufacturado extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['denominacion', 'descripcion', 'precioVenta', 'precioCosto', 'tiempoEstimado', 'categoria_id'];

    protected $table = 'articulo_manufacturado';

    public function pedidoVentaDetalles()
    {
        return $this->hasMany(PedidoVentaDetalle::class);
    }

    public function facturaVentaDetalle()
    {
        return $this->hasMany(facturaVentaDetalle::class);
    }

    public function detalles()
    {
        return $this->hasMany(ArticuloManufacturadoDetalle::class);
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaArticuloManufacturado::class, 'categoria_id');
    }

    public function imagenes()
    {
        return $this->hasMany(ImagenManufacturado::class);
    }

    public function promocionDetalles()
    {
        return $this->hasMany(PromocionDetalle::class, 'articulo_manufacturado_id');
    }

    // Costo según el precio de compra actual de cada ingrediente.
    public function precioCostoCalculado(): float
    {
        $total = 0;

        foreach ($this->detalles as $detalle) {
            $total += $detalle->cantidad * ($detalle->articuloInsumo?->precioCompra ?? 0);
        }

        return round($total, 2);
    }

    // Cuántas unidades se pueden preparar con el stock actual de la sucursal
    // (la primera si no se indica). Un ingrediente dado de baja cuenta como sin stock.
    public function stockCalculado(?int $sucursalId = null): int
    {
        $sucursalId ??= SucursalEmpresa::min('id');
        $posibles = [];

        foreach ($this->detalles as $detalle) {
            if ($detalle->cantidad <= 0) {
                continue;
            }
            $insumo = $detalle->articuloInsumo;
            if (!$insumo || $insumo->trashed()) {
                return 0;
            }
            $stock = (int) SucursalInsumo::where('articulo_insumo_id', $insumo->id)
                ->where('sucursal_empresa_id', $sucursalId)
                ->value('stockActual');
            $posibles[] = (int) floor($stock / $detalle->cantidad);
        }

        return count($posibles) ? min($posibles) : 0;
    }

    // Guarda el costo calculado en la columna precioCosto.
    public function actualizarCosto(): void
    {
        $this->load('detalles.articuloInsumo');
        $this->update(['precioCosto' => $this->precioCostoCalculado()]);
    }
}

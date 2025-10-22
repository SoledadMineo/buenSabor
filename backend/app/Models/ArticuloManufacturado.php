<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticuloManufacturado extends Model
{
    use HasFactory;

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
        return $this->belongsTo(CategoriaArticuloManufacturado::class);
    }

    public function imagenes()
    {
        return $this->hasMany(ImagenManufacturado::class);
    }

    public function promocionDetalles()
    {
        return $this->hasMany(PromocionDetalle::class, 'articulo_manufacturado_id');
    }

    public function precioCostoCalculado()
    {
        $total = 0;

        foreach ($this->detalles as $detalle) {
            $insumo = $detalle->articuloInsumo; 
            $total += $detalle->cantidad * $insumo->precioCosto;
        }

        return $total;
    }

    public function stockCalculado()
    {
        $posibles = [];

        foreach ($this->detalles as $detalle) {
            $insumo = $detalle->articuloInsumo;
            if ($detalle->cantidad > 0) {
                $posibles[] = floor($insumo->stockActual / $detalle->cantidad);
            }
        }

        return count($posibles) ? min($posibles) : 0;
    }
}

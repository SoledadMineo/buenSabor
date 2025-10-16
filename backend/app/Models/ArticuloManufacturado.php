<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticuloManufacturado extends Model
{
    use HasFactory;

    protected $fillable = ['denominacion', 'descripcion', 'precioVenta', 'precioCosto', 'tiempoEstimado'];

    public function pedidoVentaDetalles()
    {
        return $this->hasMany(PedidoVentaDetalle::class, 'articulo_manufacturado_id');
    }

}

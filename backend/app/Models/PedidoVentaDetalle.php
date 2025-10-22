<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoVentaDetalle extends Model
{
    use HasFactory;

    protected $table = 'pedido_venta_detalle';

    protected $fillable = ['pedido_venta_id', 'articulo_manufacturado_id', 'promocion_id', 'articulo_insumo_id', 'cantidad', 'subtotal'];

    public function pedidoVenta()
    {
        return $this->belongsTo(PedidoVenta::class, 'pedido_venta_id');
    }

    public function articuloManufacturado()
    {
        return $this->belongsTo(ArticuloManufacturado::class, 'articulo_manufacturado_id');
    }

    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'promocion_id');
    }

    public function articuloInsumo()
    {
        return $this->belongsTo(ArticuloInsumo::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaVentaDetalle extends Model
{
    use HasFactory;

    protected $table = 'factura_venta_detalle';

    protected $fillable = ['pedido_venta_id', 'factura_venta_id', 'cantidad', 'subtotal', 'articulo_manufacturado_id'];

    public function FacturaVenta()
    {
        return $this->belongsTo(FacturaVenta::class, 'factura_venta_id');
    }

    public function articuloManufacturado()
    {
        return $this->belongsTo(ArticuloManufacturado::class, 'articulo_manufacturado_id');
    }
}

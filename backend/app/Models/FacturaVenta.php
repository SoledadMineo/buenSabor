<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaVenta extends Model
{
    use HasFactory;

    protected $table = 'factura_venta';

    protected $fillable = ['fechaFacturacion', 'numeroComprobante', 'formaPago', 'subtotal', 'descuento', 'gastosEnvio', 'totalVenta', 'pedido_venta_id'];

    public function pedidoVenta()
    {
        return $this->belongsTo(PedidoVenta::class);
    }

    public function detalles()
    {
        return $this->hasMany(FacturaVentaDetalle::class, 'factura_venta_id');
    }

    public function datosMercadoPago()
    {
        return $this->hasOne(DatosMercadoPago::class, 'factura_venta_id');
    }
}

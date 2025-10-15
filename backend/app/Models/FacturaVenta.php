<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaVenta extends Model
{
    use HasFactory;

    protected $table = 'factura_venta';

    protected $fillable = ['fechaFacturacion', 'numeroComprobante', 'formaPago', 'subtotal', 'descuento', 'gastosEnvio', 'totalVenta'];

    public function pedidoVenta()
    {
        return $this->belongsTo(PedidoVenta::class);
    }

}

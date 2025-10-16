<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaVentaDetalle extends Model
{
    use HasFactory;

    protected $table = 'factura_venta_detalle';

    protected $fillable = ['cantidad', 'subtotal'];

    public function FacturaVenta()
    {
        return $this->belongsTo(FacturaVenta::class, 'factura_venta_id');
    }
}

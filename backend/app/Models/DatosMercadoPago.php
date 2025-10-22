<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatosMercadoPago extends Model
{
    use HasFactory;

    protected $table = 'datos_mercado_pago';

    protected $fillable = ['factura_venta_id', 'date_created', 'date_approved', 'date_last_updated', 'payment_type_id', 'payment_method_id', 'status', 'status_detail'];

    public function facturaVenta()
    {
        return $this->belongsTo(FacturaVenta::class, 'factura_venta_id');
    }
}

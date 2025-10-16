<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    use HasFactory;

    protected $table = 'promocion';

    protected $fillable = ['denominacion', 'fechaDesde', 'fechaHasta', 'descuento'];

    public function detalles()
    {
        return $this->hasMany(PromocionDetalle::class, 'promocion_detalle_id');
    }

    public function pedidoVentaDetalles()
    {
        return $this->hasMany(PedidoVentaDetalle::class, 'promocion_id');
    }


}

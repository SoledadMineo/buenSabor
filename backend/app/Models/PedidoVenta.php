<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoVenta extends Model
{
    use HasFactory;

    protected $table = 'pedido_venta';

    protected $fillable = ['horaEstimadaFinalizacion', 'subtotal', 'descuento', 'gastosEnvio', 'total', 'totalCosto', 'estado', 'tipoEnvio', 'formaPago', 'fechaPedido', 'sucursal_empresa_id', 'empleado_id'];

    public function sucursalEmpresa()
    {
        return $this->belongsTo(SucursalEmpresa::class, 'sucursal_empresa_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function facturas()
    {
        return $this->hasOne(FacturaVenta::class);
    }

    public function detalles()
    {
        return $this->hasMany(PedidoVentaDetalle::class, 'pedido_venta_id');
    }
}

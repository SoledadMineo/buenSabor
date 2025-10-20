<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SucursalEmpresa extends Model
{
    use HasFactory;

    protected $table = 'sucursal_empresa';

    protected $fillable = ['nombre', 'horarioApertura', 'horarioCierre', 'empresa_id'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function domicilio()
    {
        return $this->hasOne(Domicilio::class);
    }

    public function pedidosVenta()
    {
        return $this->hasMany(PedidoVenta::class);
    }

    public function sucursalesInsumos()
    {
        return $this->hasMany(SucursalInsumo::class, 'sucursal_empresa_id');
    }
}

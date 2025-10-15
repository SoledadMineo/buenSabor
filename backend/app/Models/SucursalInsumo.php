<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SucursalInsumo extends Model
{
    use HasFactory;

    protected $fillable = ['stockActual', 'stockMinimo', 'stockMaximo'];

    public function sucursalEmpresa()
    {
        return $this->belongsTo(SucursalEmpresa::class, 'sucursal_empresa_id');
    }
}

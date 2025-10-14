<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'razonSocial', 'cuil'];

    public function sucursales()
    {
        return $this->hasMany(SucursalEmpresa::class, 'empresa_id');
    }
}

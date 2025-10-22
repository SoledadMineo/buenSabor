<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'unidad_medidas';

    protected $fillable = ['denominacion'];

    public function articuloInsumos()
    {
        return $this->hasMany(ArticuloInsumo::class, 'unidad_medida_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagenInsumo extends Model
{
    use HasFactory;
    
    protected $table = 'imagen_insumos';

    protected $fillable = ['denominacion'];

    public function articuloInsumos()
    {
        return $this->hasMany(ArticuloInsumo::class, 'imagen_insumo_id');
    }
}

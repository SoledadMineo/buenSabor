<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaArticulo extends Model
{
    use HasFactory;
    
    protected $table = 'categoria_articulos';

    protected $fillable = ['denominacion'];

    public function articuloInsumos()
    {
        return $this->hasMany(ArticuloInsumo::class, 'categoria_articulo_id');
    }
}

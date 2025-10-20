<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaArticuloManufacturado extends Model
{
    use HasFactory;

    protected $table = 'categoria_articulo_manufacturado';

    protected $fillable = [
        'denominacion'
    ];

    public function articuloManufacturado()
    {
        return $this->hasMany(ArticuloManufacturado::class);
    }

}

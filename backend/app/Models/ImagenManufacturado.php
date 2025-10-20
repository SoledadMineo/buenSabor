<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagenManufacturado extends Model
{
    use HasFactory;

    protected $fillable = ['denominacion'];

    protected $table = 'imagen_manufacturado';

    public function articulo()
    {
        return $this->belongTo(ArticuloManufacturado::class);
    }

}

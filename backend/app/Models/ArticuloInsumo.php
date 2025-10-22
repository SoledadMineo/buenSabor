<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticuloInsumo extends Model
{
    use HasFactory;

    protected $table = 'articulo_insumos';

    protected $fillable = [
        'denominacion',
        'precioCompra',
        'precioVenta',
        'esParaElaborar',
        'categoria_articulo_id',
        'imagen_insumo_id',
        'unidad_medida_id',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaArticulo::class, 'categoria_articulo_id');
    }

    public function imagen()
    {
        return $this->belongsTo(ImagenInsumo::class, 'imagen_insumo_id');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }
}

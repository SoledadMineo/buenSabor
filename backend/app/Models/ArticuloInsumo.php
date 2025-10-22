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
        'padre_id'
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

    public function padre()
    {
        return $this->belongsTo(ArticuloInsumo::class, 'padre_id'); 
    }

    public function hijo()
    {
        return $this->hasMany(ArticuloInsumo::class, 'padre_id');
    }

    public function articulosManufacturadosDetalle()
    {
        return $this->hasMany(ArticuloManufacturadoDetalle::class);
    }

    public function facturaVentaDetalles()
    {
        return $this->hasMany(FacturaVentaDetalle::class);
    }

    public function pedidoVentaDetalles()
    {
        return $this->hasMany(PedidoVentaDetalle::class);
    }

    public function promocionDetalles()
    {
        return $this->hasMany(PromocionDetalle::class);
    }

    public function sucursales()
    {
        return $this->hasMany(SucursalInsumo::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticuloInsumo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'articulo_insumo';

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

    protected $casts = [
        'esParaElaborar' => 'boolean',
    ];

    // tipo: 'elaboracion' (se usa en recetas) o 'venta' (se vende directo, ej. bebidas).
    public function scopeDeTipo($query, ?string $tipo)
    {
        return match ($tipo) {
            'elaboracion' => $query->where('esParaElaborar', true),
            'venta' => $query->where('esParaElaborar', false),
            default => $query,
        };
    }

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
        return $this->hasMany(SucursalInsumo::class, 'articulo_insumo_id');
    }
}

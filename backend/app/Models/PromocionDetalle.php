<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromocionDetalle extends Model
{
    use HasFactory;

    protected $table = 'promocion_detalle';

    protected $fillable = ['cantidad'];

    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'promocion_id');
    }

}

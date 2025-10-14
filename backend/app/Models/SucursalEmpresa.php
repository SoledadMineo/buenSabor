<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SucursalEmpresa extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'horarioApertura', 'horarioCierre', 'empresa_id'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function domicilio()
    {
        return $this->belongsTo(Domicilio::class, 'domicilio_id');
    }
}

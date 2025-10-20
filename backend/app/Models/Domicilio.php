<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domicilio extends Model
{
    use HasFactory;

    protected $fillable = ['calle', 'numero', 'cp', 'localidad_id'];

    protected $table = 'domicilio';

    public function sucursales()
    {
        return $this->belongsTo(SucursalEmpresa::class);
    }

    public function localidad()
    {
        return $this->belongsTo(Localidad::class, 'localidad_id');
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }
}

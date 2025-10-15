<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'apellido', 'telefono', 'email'];

    public function domicilio()
    {
        return $this->hasOne(Domicilio::class);
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class);
    }
}

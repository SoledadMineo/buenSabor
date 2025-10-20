<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'apellido', 'telefono', 'email', 'usuario_id', 'domicilio_id'];

    protected $table = 'cliente';

    public function domicilio()
    {
        return $this->belongsTo(Domicilio::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

}

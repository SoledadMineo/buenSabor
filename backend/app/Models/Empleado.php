<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Rol;

class Empleado extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'apellido', 'telefono', 'email', 'perfil'];

    protected $table = 'empleado';

    protected $casts = [
        'perfil' => Rol::class,
    ];

    public function pedidos()
    {
        return $this->hasMany(PedidoVenta::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['authOId', 'username', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

    protected $table = 'usuario';

    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }

    public function empleado()
    {
        return $this->hasOne(Empleado::class);
    }
}

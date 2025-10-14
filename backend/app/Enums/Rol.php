<?php

namespace App\Enums;

enum Rol: string
{
    case ADMIN = 'admin';
    case EMPLEADO = 'empleado';
    case CLIENTE = 'cliente';
}

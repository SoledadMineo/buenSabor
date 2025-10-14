<?php

namespace App\Enums;

enum Estado: string
{
    case PREPARACION = 'preparacion';
    case PENDIENTE = 'pendiente';
    case CANCELADO = 'cancelado';
    case RECHAZADO = 'rechazado';
    case ENTREGADO = 'entregado';
}

<?php

namespace App\Enums;

enum TipoEnvio: string
{
    case DELIVERY = 'delivery';
    case TAKEAWAY = 'takeAway';
}

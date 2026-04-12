<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;

class UnidadMedidaController extends Controller
{
    public function medidas()
    {
        return UnidadMedida::all();
    }
}

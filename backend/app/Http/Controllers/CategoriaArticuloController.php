<?php

namespace App\Http\Controllers;

use App\Models\CategoriaArticulo;

class CategoriaArticuloController extends Controller
{
    public function categorias()
    {
        return CategoriaArticulo::all();
    }
}

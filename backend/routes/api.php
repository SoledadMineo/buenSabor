<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\CategoriaArticuloController;
use App\Http\Controllers\UnidadMedidaController;

Route::get('/insumos', [ArticuloController::class, 'insumos']);
Route::post('/insumos', [ArticuloController::class, 'storeInsumo']);
Route::get('/insumos/{id}', [ArticuloController::class, 'insumoById']);

Route::get('/manufacturados', [ArticuloController::class, 'manufacturados']);
Route::get('/manufacturados/{id}', [ArticuloController::class, 'manufacturadoById']);

Route::get('/articulos', [ArticuloController::class, 'todos']);

Route::get('/articulos/admin', [ArticuloController::class, 'adminIndex']);

Route::get('/unidades-medida', [UnidadMedidaController::class, 'medidas']);

Route::get('/categorias-articulo', [CategoriaArticuloController::class, 'categorias']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

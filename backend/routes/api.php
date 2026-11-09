<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\CategoriaArticuloController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\AuthController;

Route::get('/insumos', [ArticuloController::class, 'insumos']);
Route::middleware(['auth:sanctum', 'role:admin'])->post('/insumos', [ArticuloController::class, 'storeInsumo']);
Route::get('/insumos/{id}', [ArticuloController::class, 'insumoById']);

Route::get('/manufacturados', [ArticuloController::class, 'manufacturados']);
Route::get('/manufacturados/{id}', [ArticuloController::class, 'manufacturadoById']);

Route::get('/articulos', [ArticuloController::class, 'todos']);

Route::middleware(['auth:sanctum', 'role:admin'])->get('/articulos/admin', [ArticuloController::class, 'adminIndex']);

Route::get('/unidades-medida', [UnidadMedidaController::class, 'medidas']);

Route::get('/categorias-articulo', [CategoriaArticuloController::class, 'categorias']);

Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'sesion']);

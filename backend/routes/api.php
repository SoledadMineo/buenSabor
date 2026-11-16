<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\CategoriaArticuloController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ManufacturadoController;
use App\Http\Controllers\CategoriaManufacturadoController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\AuthController;

Route::get('/insumos', [ArticuloController::class, 'insumos']);
Route::get('/insumos/{id}', [ArticuloController::class, 'insumoById']);

Route::get('/manufacturados', [ManufacturadoController::class, 'index']);
Route::get('/manufacturados/{id}', [ManufacturadoController::class, 'show'])->whereNumber('id');
Route::get('/categorias-manufacturado', [CategoriaManufacturadoController::class, 'index']);

Route::get('/articulos', [ArticuloController::class, 'todos']);

// Revisa el carrito (precios, bajas y stock) sin modificar nada.
Route::post('/carrito/validar', [CarritoController::class, 'validar'])->middleware('throttle:60,1');

Route::get('/unidades-medida', [UnidadMedidaController::class, 'medidas']);

Route::get('/categorias-articulo', [CategoriaArticuloController::class, 'categorias']);

Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'sesion']);

// Administración (solo admin)
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/articulos/admin', [ArticuloController::class, 'adminIndex']);

    Route::post('/insumos', [ArticuloController::class, 'storeInsumo']);
    // Con archivos (imagen) el navegador envía POST + _method=PUT.
    Route::put('/insumos/{id}', [ArticuloController::class, 'updateInsumo']);
    Route::delete('/insumos/{id}', [ArticuloController::class, 'destroyInsumo']);
    Route::post('/insumos/{id}/restaurar', [ArticuloController::class, 'restoreInsumo']);

    Route::post('/categorias-articulo', [CategoriaArticuloController::class, 'store']);
    Route::put('/categorias-articulo/{id}', [CategoriaArticuloController::class, 'update']);
    Route::delete('/categorias-articulo/{id}', [CategoriaArticuloController::class, 'destroy']);

    Route::post('/unidades-medida', [UnidadMedidaController::class, 'store']);
    Route::put('/unidades-medida/{id}', [UnidadMedidaController::class, 'update']);
    Route::delete('/unidades-medida/{id}', [UnidadMedidaController::class, 'destroy']);

    // Manufacturados. Con imagen se envía POST + _method=PUT.
    Route::get('/manufacturados/admin', [ManufacturadoController::class, 'admin']);
    Route::post('/manufacturados', [ManufacturadoController::class, 'store']);
    Route::put('/manufacturados/{id}', [ManufacturadoController::class, 'update']);
    Route::delete('/manufacturados/{id}', [ManufacturadoController::class, 'destroy']);
    Route::post('/manufacturados/{id}/restaurar', [ManufacturadoController::class, 'restore']);

    Route::post('/categorias-manufacturado', [CategoriaManufacturadoController::class, 'store']);
    Route::put('/categorias-manufacturado/{id}', [CategoriaManufacturadoController::class, 'update']);
    Route::delete('/categorias-manufacturado/{id}', [CategoriaManufacturadoController::class, 'destroy']);

    Route::get('/sucursales', [StockController::class, 'sucursales']);
    Route::get('/stock/alertas', [StockController::class, 'alertas']);
    Route::get('/insumos/{id}/stock', [StockController::class, 'porInsumo']);
    Route::put('/insumos/{id}/stock', [StockController::class, 'definir']);
    Route::post('/insumos/{id}/stock/ajuste', [StockController::class, 'ajustar']);
});

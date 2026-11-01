<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// El grupo web proporciona cookies, sesion y proteccion CSRF al login.
Route::post('/api/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/api/logout', [AuthController::class, 'logout']);

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

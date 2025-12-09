<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageViewsController;
use App\Http\Controllers\PagoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas para contador de visitas de páginas
Route::get('/page-views/current', [PageViewsController::class, 'getCurrentPageViews'])->name('page-views.current');
Route::get('/page-views/stats', [PageViewsController::class, 'getAllStats'])->name('page-views.stats');
Route::post('/page-views/reset', [PageViewsController::class, 'resetPageViews'])->middleware('auth:sanctum')->name('page-views.reset');

// Ruta para obtener estado de pago (para verificación de QR)
Route::get('/pagos/{id}/estado', [PagoController::class, 'obtenerEstado']);

<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\BarberoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PagoFacilController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\UsuarioController;
use App\Models\Servicio;

use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// OAuth routes
Route::get('/auth/{provider}', [SocialAuthController::class, 'redirectToProvider'])
    ->name('auth.social');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])
    ->name('auth.social.callback');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
// Rutas públicas para el catálogo de servicios - API JSON
Route::get('/api/servicios-catalogo', function () {
    return response()->json(
        Servicio::get()
    );
})->name('servicios.catalogo.api');

Route::get('/api/barberos-disponibles', [BarberoController::class, 'disponibles'])
    ->name('barberos.disponibles.api');

Route::get('/api/horarios-disponibles', [BarberoController::class, 'horariosDisponibles'])
    ->name('horarios.disponibles.api');

// Callback de PagoFácil (debe estar FUERA del middleware de auth porque lo llama PagoFácil)
Route::post('/api/pago-facil/callback', [PagoFacilController::class, 'callback'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('pagofacil.callback');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'servicios' => Servicio::get()
        ]);
    })->name('dashboard');

    // Ruta específica para catálogo de servicios (solo clientes)
    Route::get('/servicios-catalogo', function () {
        return Inertia::render('Servicios/Catalogo', [
            'servicios' => Servicio::get()
        ]);
    })->middleware('role:cliente')->name('servicios.catalogo');

    // Rutas solo para propietario
    Route::middleware('role:propietario')->group(function () {
        Route::resource('categorias', CategoriaController::class);
        Route::resource('productos', ProductoController::class);
        Route::resource('servicios', ServicioController::class);
        Route::resource('barberos', BarberoController::class);
        Route::resource('clientes', ClienteController::class);
        Route::resource('usuarios', UsuarioController::class);
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

        // Estadísticas de visitas (solo propietario)
        Route::get('/estadisticas/visitas', function () {
            return Inertia::render('Estadisticas/PageViews');
        })->name('estadisticas.visitas');
    });

    // Rutas para propietario y barbero
    Route::middleware('role:propietario,barbero')->group(function () {
        Route::resource('horarios', HorarioController::class);
        Route::get('/api/reservas/horarios-disponibles', [ReservaController::class, 'horariosDisponibles'])->name('reservas.horarios-disponibles');
    });

    // Rutas para propietario, barbero y cliente (cada uno ve sus propios datos)
    Route::middleware('role:propietario,barbero,cliente')->group(function () {
        Route::resource('reservas', ReservaController::class);
        Route::resource('pagos', PagoController::class);
    });

    // Rutas para todos (clientes también pueden pagar reservas)
    Route::get('/pagar-reserva', [PagoController::class, 'pagarReserva'])->name('pagos.pagar-reserva');

    // Rutas para PagoFácil (requieren autenticación)
    Route::post('/api/pago-facil/generar-qr', [PagoFacilController::class, 'generarQR'])->name('pagofacil.generar-qr');
    Route::post('/api/pago-facil/consultar-estado', [PagoFacilController::class, 'consultarEstado'])->name('pagofacil.consultar-estado');
    Route::get('/api/pagos/{id}/estado', [PagoController::class, 'obtenerEstado'])->name('pagos.estado');
});

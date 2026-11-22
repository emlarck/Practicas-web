<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\TemporadaController;
use App\Http\Controllers\TorneoController;
use App\Http\Controllers\PartidoController;
use App\Http\Controllers\JugadorController;
use App\Http\Controllers\EstadisticaController;
use App\Http\Controllers\CanchaController;
use App\Http\Controllers\ArbitroController;
use App\Http\Controllers\PatrocinadorController;
use App\Http\Controllers\PosicionController;
use App\Http\Controllers\UniformeController;
use App\Http\Controllers\PartidoJugadorController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;

// --- RUTAS PÚBLICAS DE AUTENTICACIÓN Y 2FA ---
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'store']);

// Ruta firmada temporal para el código 2FA
Route::get('/two-factor/{user}', [TwoFactorController::class, 'index'])
    ->name('two-factor.index')
    ->middleware('signed');

Route::post('/two-factor', [TwoFactorController::class, 'verify'])->name('two-factor.verify');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');


// --- RUTAS PROTEGIDAS (Requieren Login + 2FA Verificado) ---

// Redireccionar la raíz (/) hacia /inicio
Route::get('/', function () {
    return redirect()->route('inicio');
});

Route::middleware(['auth'])->group(function () {

Route::middleware(['role:admin,empleado'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });

    // Rutas EXCLUSIVAS para ADMIN (eliminación lógica/permanente y recuperación)
    Route::middleware(['role:admin'])->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/users-trashed', [UserController::class, 'trashed'])->name('users.trashed');
        Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    });

    // Ruta de inicio en /inicio
    Route::get('/inicio', function () {
        if (!session('two_factor_verified')) {
            return redirect()->route('login');
        }
        return view('welcome');
    })->name('inicio');
    
    // Recursos de tu sistema de básquetbol
    Route::resource('equipos', EquipoController::class);
    Route::resource('temporadas', TemporadaController::class);
    Route::resource('torneos', TorneoController::class);
    Route::resource('partidos', PartidoController::class);
    Route::resource('jugadores', JugadorController::class);
    Route::resource('estadisticas', EstadisticaController::class);
    Route::resource('canchas', CanchaController::class);
    Route::resource('arbitros', ArbitroController::class);
    Route::resource('patrocinadores', PatrocinadorController::class);
    Route::resource('posiciones', PosicionController::class);
    Route::resource('uniformes', UniformeController::class);
    Route::resource('convocatorias', PartidoJugadorController::class)->except(['update']);
});

<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('games.index')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Partidas y juego del gato (Jugador y Admin)
    Route::middleware('role:jugador,admin')->group(function () {
        Route::get('/games', [GameController::class, 'index'])->name('games.index');
        Route::post('/games', [GameController::class, 'store'])->name('games.store');
        Route::post('/games/{game}/join', [GameController::class, 'join'])->name('games.join');
        Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
        Route::get('/games/{game}/state', [GameController::class, 'state'])->name('games.state');
        Route::post('/games/{game}/move', [GameController::class, 'move'])->name('games.move');
        Route::post('/games/{game}/leave', [GameController::class, 'leave'])->name('games.leave');
    });

    // Administración exclusiva (Cancelar partidas y CRUD de usuarios)
    Route::middleware('role:admin')->group(function () {
        Route::post('/games/{game}/cancel', [GameController::class, 'cancel'])->name('games.cancel');

        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    });
});
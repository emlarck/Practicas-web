<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComputerController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('computers.index')
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

    // Equipos: consulta disponible para Estudiante y Admin
    Route::middleware('role:admin,estudiante')->group(function () {
        Route::get('/computers', [ComputerController::class, 'index'])->name('computers.index');
    });

    // Gestión de equipos: exclusiva para Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/computers/create', [ComputerController::class, 'create'])->name('computers.create');
        Route::post('/computers', [ComputerController::class, 'store'])->name('computers.store');
        Route::get('/computers/{computer}/edit', [ComputerController::class, 'edit'])->name('computers.edit');
        Route::put('/computers/{computer}', [ComputerController::class, 'update'])->name('computers.update');
        Route::delete('/computers/{computer}', [ComputerController::class, 'destroy'])->name('computers.destroy');
    });

    // Reservaciones: reservar, ver propias/todas y cancelar
    Route::middleware('role:admin,estudiante')->group(function () {
        Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::post('/computers/{computer}/reserve', [ReservationController::class, 'store'])->name('reservations.store');
        Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    });
});
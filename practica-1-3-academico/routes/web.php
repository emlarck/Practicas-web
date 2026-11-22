<?php

use App\Http\Controllers\AcademicRecordController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('records.index')
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

    // Consulta disponible para Profesor y Alumno
    Route::middleware('role:profesor,alumno')->group(function () {
        Route::get('/records', [AcademicRecordController::class, 'index'])->name('records.index');
    });

    // Gestión completa (CRUD) exclusiva para Profesor
    Route::middleware('role:profesor')->group(function () {
        Route::get('/records/create', [AcademicRecordController::class, 'create'])->name('records.create');
        Route::post('/records', [AcademicRecordController::class, 'store'])->name('records.store');
        Route::get('/records/{record}/edit', [AcademicRecordController::class, 'edit'])->name('records.edit');
        Route::put('/records/{record}', [AcademicRecordController::class, 'update'])->name('records.update');
        Route::delete('/records/{record}', [AcademicRecordController::class, 'destroy'])->name('records.destroy');
    });
});
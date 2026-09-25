<?php

use App\Http\Controllers\DailyRecordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/records/create', [DailyRecordController::class, 'create'])->name('records.create');
    Route::post('/records', [DailyRecordController::class, 'store'])->name('records.store');
    Route::get('/records/search', [HomeController::class, 'search'])->name('records.search');
    Route::get('/records/{record}/edit', [DailyRecordController::class, 'edit'])->name('records.edit');
    Route::patch('/records/{record}', [DailyRecordController::class, 'update'])->name('records.update');
    Route::delete('/records/{record}', [DailyRecordController::class, 'destroy'])->name('records.destroy');
    Route::get('/records/{record}', [DailyRecordController::class, 'show'])->name('records.show');
    Route::get('/register/complete', [RegisterController::class, 'complete'])->name('register.complete');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

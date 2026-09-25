<?php

use App\Http\Controllers\DailyRecordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImprovementController;

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\UserController;

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/community', [CommunityController::class, 'index'])->name('community.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/follow', [FollowController::class, 'store'])->name('users.follow.store');
    Route::delete('/users/{user}/follow', [FollowController::class, 'destroy'])->name('users.follow.destroy');
    Route::get('/records/create', [DailyRecordController::class, 'create'])->name('records.create');
    Route::post('/records', [DailyRecordController::class, 'store'])->name('records.store');
    Route::get('/records/search', [HomeController::class, 'search'])->name('records.search');
    Route::get('/records/{record}/edit', [DailyRecordController::class, 'edit'])->name('records.edit');
    Route::get('/records/{record}/improvement', [ImprovementController::class, 'edit'])->name('records.improvement.edit');
    Route::patch('/records/{record}/improvement', [ImprovementController::class, 'update'])->name('records.improvement.update');
    Route::patch('/records/{record}', [DailyRecordController::class, 'update'])->name('records.update');
    Route::delete('/records/{record}', [DailyRecordController::class, 'destroy'])->name('records.destroy');
    Route::get('/records/{record}', [DailyRecordController::class, 'show'])->name('records.show');
    Route::get('/register/complete', [RegisterController::class, 'complete'])->name('register.complete');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

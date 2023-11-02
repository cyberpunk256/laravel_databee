<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "admin" middleware group. Make something great!
|
*/



Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('admin.login.create');
    Route::post('/login', [LoginController::class, 'store'])->name('admin.login.store');
});

Route::middleware('auth.admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.index');
    Route::post('/logout', [DashboardController::class, 'destroy'])->name('admin.logout');
});
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MediaController;
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



Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth.admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::post('/logout', [DashboardController::class, 'destroy'])->name('logout');
    Route::resource('/user', UserController::class)->except(['show']);
    Route::post('/media/presigned_url', [MediaController::class, 'getPresignedUrl'])->name('media.presigned_url');
    Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');
    Route::resource('/media', MediaController::class)->except(['show']);
});

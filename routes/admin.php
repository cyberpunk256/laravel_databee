<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MediaController;

use App\Http\Controllers\S3Controller;
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
    Route::resource('/media', MediaController::class)->except(['show']);

    Route::post('/s3/designed_url', [S3Controller::class, 'getPresignedUrl'])->name('s3.designed_url');
});

Route::post('/s3/upload', [S3Controller::class, 'upload'])->name('s3.upload');
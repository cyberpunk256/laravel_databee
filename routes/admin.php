<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\CaptureController;
use App\Http\Controllers\Admin\SettingController;
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
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth.admin:1,2,3')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('media/preview', [MediaController::class, 'preview'])->name('media.preview');
    Route::post('media/delete_records', [MediaController::class, 'deleteRecords'])->name('media.deleteRecords');
    Route::post('media/new_presigned_url', [MediaController::class, 'createPresignedUrl'])->name('media.createPresignedUrl');
    Route::post('media/{id}/update_status', [MediaController::class, 'updateStatus'])->name('media.updateStatus');
    Route::resource('media', MediaController::class)->except(['show']);
});

   
Route::middleware('auth.admin:1')->group(function () {
    Route::resource('admin', AdminController::class)->except(['show']);
    Route::resource('user', UserController::class)->except(['show']);
    Route::resource('group', GroupController::class)->except(['show']);
    Route::resource('capture', CaptureController::class)->except(['show','create','update']);
    Route::get('setting', [SettingController::class, 'index'])->name('setting.index');
    Route::post('setting', [SettingController::class, 'update'])->name('setting.update');
    Route::get('bulk_upload', [MediaController::class, 'bulkUpload'])->name('media.bulkUpload');
    Route::post('bulk_upload', [MediaController::class, 'bulkUploadStore'])->name('media.bulkUploadStore');
});

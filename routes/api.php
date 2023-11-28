<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\S3Controller;
use App\Http\Controllers\CaptureController;
use App\Http\Controllers\MediaController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/user', function (Request $request) {
    //     return $request->user();
    // });
    Route::post('media/presigned_url', [MediaController::class, 'getPresignedUrl'])->name('media.getPresignedUrl');
    Route::get('capture', [CaptureController::class, 'index'])->name('capture.index');
    Route::post('capture', [CaptureController::class, 'store'])->name('capture.store');
    Route::post('capture/delete_records ', [CaptureController::class, 'deleteRecords'])->name('capture.deleteRecords');
    Route::get('file', [MediaController::class, 'getFile'])->name('media.getFile');
});
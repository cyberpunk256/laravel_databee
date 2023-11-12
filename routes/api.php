<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\S3Controller;
use App\Http\Controllers\CaptureController;
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
    Route::get('capture', [CaptureController::class, 'index'])->name('capture.index');
    Route::post('capture/file_upload', [CaptureController::class, 'fileUpload'])->name('capture.fileUpload');
    Route::post('capture/delete_records ', [CaptureController::class, 'deleteRecords'])->name('capture.deleteRecords');
});
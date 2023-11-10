<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\S3Controller;
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


Route::get('getVideo', [S3Controller::class, 'getVideo'])->name('getVideo');
Route::get('getFile', [S3Controller::class, 'getFile'])->name('getFile');
// Route::middleware('auth:sanctum')->group(function () {
//     // Route::get('/user', function (Request $request) {
//     //     return $request->user();
//     // });
// });
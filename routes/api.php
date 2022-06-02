<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('login/{provider}', [AuthController::class,'redirectToProvider']);
Route::get('login/{provider}/callback', [AuthController::class,'handleProviderCallback']);

Route::post('extract',[ServiceController::class,'extract']);
Route::post('merge',[ServiceController::class,'merge']);
Route::post('imagetopdf',[ServiceController::class,'imageToPdf']);
Route::post('compress',[ServiceController::class,'compress']);

Route::middleware('auth:api')->group(function () {
    // Route::resource('posts', PostController::class);
});

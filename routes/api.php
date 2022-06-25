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

Route::post('sign-up', [AuthController::class, 'signUp'])->name('signup');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('forget-password', [AuthController::class, 'forgetPassword']);
Route::post('verify-otp', [AuthController::class, 'verifyOTP']);
Route::post('set-new-password', [AuthController::class, 'setNewPassword']);

Route::get('login/{provider}', [AuthController::class, 'redirectToProvider']);
Route::get('login/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

Route::prefix('admin')->group(function () {
    Route::post('sign-up', [AuthController::class, 'adminSignUp']);
    Route::post('login', [AuthController::class, 'adminLogin']);
});

Route::middleware(['auth:api,admin'])->group(function () {
    Route::get('get-extract', [ServiceController::class, 'getExtractData']);
    Route::post('extract', [ServiceController::class, 'extract']);
    Route::post('extract-invoice', [ServiceController::class, 'extractInvoice']);
    Route::post('merge', [ServiceController::class, 'merge']);
    Route::post('imagetopdf', [ServiceController::class, 'imageToPdf']);
    Route::post('compress', [ServiceController::class, 'compress']);
    Route::get('logout', [AuthController::class, 'logout']);
});



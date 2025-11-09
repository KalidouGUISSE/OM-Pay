<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompteController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



// Routes d'authentification (non protégées)
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// Routes protégées
Route::middleware(['auth:api', 'logging'])->group(function () {
    Route::prefix('v1/auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('v1')->group(function () {
        Route::get('/comptes', [CompteController::class, 'index'])->middleware('role:client,admin');
        Route::get('/comptes/{id}', [CompteController::class, 'show'])->middleware('role:client,admin');
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});

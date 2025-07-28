<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\GetBalanceController;
use App\Http\Controllers\Api\V1\ShanLaunchGameController;
use App\Http\Controllers\Api\V1\Auth\AuthController;

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

// login route
Route::post('login', [AuthController::class, 'login']);

// client get balance

Route::group(['prefix' => 'shan'], function () {
    Route::post('balance', [GetBalanceController::class, 'getBalance']);
    Route::post('/client/balance-update', [BalanceUpdateCallbackController::class, 'handleBalanceUpdate']); 
});

Route::middleware(['auth:sanctum'])->group(function () {
    // route prefix shan 
    Route::group(['prefix' => 'shankomee'], function () {
        Route::post('launch-game', [ShanLaunchGameController::class, 'launchGame']);
    });
});
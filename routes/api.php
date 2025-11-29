<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\HouseHoldController;
use App\Http\Controllers\User\UserController;

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/me', [UserController::class, 'me']);
    Route::get('/households', [HouseHoldController::class, 'index']);
    Route::post('/households', [HouseHoldController::class, 'store']);
    Route::post('/households/join', [HouseHoldController::class, 'join']);
});


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

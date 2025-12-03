<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HouseHoldController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PantryItemController;
use App\Http\Controllers\RecipeController;

// Authenticated routes
Route::middleware('auth:api')->group(function () {
    // User related routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/me', [UserController::class, 'me']);

    // Household related routes
    Route::get('/households', [HouseHoldController::class, 'get']);
    Route::post('/households', [HouseHoldController::class, 'create']);
    Route::post('/households/join', [HouseHoldController::class, 'join']);
    
    // Pantry item related routes
    Route::get('/households/{householdId}/pantry', [PantryItemController::class, 'get']);
    Route::post('/households/{householdId}/pantry', [PantryItemController::class, 'create']);
    Route::put('/pantry/{id}', [PantryItemController::class, 'update']);
    Route::delete('/pantry/{id}', [PantryItemController::class, 'delete']);
    
    // Recipe related routes
    Route::get('/households/{householdId}/recipes', [RecipeController::class, 'get']);
    Route::post('/households/{householdId}/recipes', [RecipeController::class, 'create']);
    Route::put('/recipes/{id}', [RecipeController::class, 'update']);
    Route::delete('/recipes/{id}', [RecipeController::class, 'delete']);
});

//Unauthenticated routes
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);

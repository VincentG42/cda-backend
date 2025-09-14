<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserTypeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SeasonController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EncounterController;



Route::post('/login', [AuthController::class, 'login']);



// Route::middleware(['auth:sanctum', 'can:access-admin-panel'])->group(function () {

    //Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // User
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // User Type
    Route::get('/user-types', [UserTypeController::class, 'index']);
    Route::get('/user-types/{id}', [UserTypeController::class, 'show']);
    Route::post('/user-types', [UserTypeController::class, 'store']);
    Route::put('/user-types/{id}', [UserTypeController::class, 'update']);
    Route::delete('/user-types/{id}', [UserTypeController::class, 'destroy']);

    // Category
    Route::get('/categories', [CategoryController::class, 'index']);

    // Season
    Route::get('/seasons', [SeasonController::class, 'index']);

    // Team
    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{team}/players', [TeamController::class, 'addPlayer'])->name('teams.players.add');
    Route::delete('teams/{team}/players/{player}', [TeamController::class, 'removePlayer'])->name('teams.players.remove');

    // Event
    Route::apiResource('events', EventController::class);

    // Encounter
    Route::apiResource('encounters', EncounterController::class);
    Route::post('encounters/{encounter}/stats', [EncounterController::class, 'uploadStats'])->name('encounters.stats.upload');
// });

<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\EncounterController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\SeasonController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserTypeController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/contact', [ContactController::class, 'send']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Publicly accessible event routes
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);

// Authenticated routes (any user type)
Route::middleware(['auth:sanctum'])->group(function () {
    // My Profile
    Route::get('/me', [AuthController::class, 'me']);
    // My Teams
    Route::get('/me/teams', [AuthController::class, 'myTeams']);

    // My Dashboard
    Route::get('/me/dashboard', [AuthController::class, 'myDashboard']);

    // My Matches
    Route::get('/me/matches', [AuthController::class, 'myMatches']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Admin/privileged routes
Route::middleware(['auth:sanctum', 'can:access-admin-panel'])->group(function () {

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
    Route::apiResource('categories', CategoryController::class);

    // Season
    Route::apiResource('seasons', SeasonController::class);

    // Team
    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{team}/players', [TeamController::class, 'addPlayer'])->name('teams.players.add');
    Route::delete('teams/{team}/players/{player}', [TeamController::class, 'removePlayer'])->name('teams.players.remove');
    Route::post('teams/{team}/coach', [TeamController::class, 'assignCoach'])->name('teams.coach.assign');

    // Event (Admin-only actions)
    Route::apiResource('events', EventController::class)->except(['index', 'show']);

    // Encounter
    Route::apiResource('encounters', EncounterController::class);
    Route::put('encounters/{encounter}/result', [EncounterController::class, 'updateResult'])->name('encounters.updateResult');
    Route::post('encounters/{encounter}/stats', [EncounterController::class, 'uploadStats'])->name('encounters.stats.upload');
});

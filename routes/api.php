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

// TEMPORARY PUBLIC TEST ROUTE - NO AUTH, NO POLICY
Route::get('/public-test-team-1', function () {
    $team = \App\Models\Team::find(1);
    if (!$team) {
        return response()->json(['message' => 'Team 1 not found in public test'], 404);
    }

    $teamStatsService = app(\App\Domain\Statistics\Services\TeamStatsService::class);

    $overview = $teamStatsService->getSeasonOverview($team);
    $analysis = $teamStatsService->getPointsConcededAnalysis($team);

    return response()->json([
        'overview' => $overview,
        'analysis' => $analysis,
    ]);
});

Route::get('/public-test-team-stats', function () {
    $team = \App\Models\Team::find(1); // Hardcode team ID 1
    if (!$team) {
        return response()->json(['message' => 'Team 1 not found for stats test'], 404);
    }

    $teamStatsService = app(\App\Domain\Statistics\Services\TeamStatsService::class);

    $overview = $teamStatsService->getSeasonOverview($team);
    $analysis = $teamStatsService->getPointsConcededAnalysis($team);

    return response()->json([
        'overview' => $overview,
        'analysis' => $analysis,
    ]);
});
// END TEMPORARY PUBLIC TEST ROUTE

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

    // Player Stats
    Route::get('/players/{user}/stats/averages', [App\Http\Controllers\Api\PlayerStatsController::class, 'getAverages']);
    Route::get('/players/{user}/stats/historical/{stat}', [App\Http\Controllers\Api\PlayerStatsController::class, 'getHistorical'])->where('stat', '[a-zA-Z]+');
    Route::get('/players/{user}/match/{encounter}/stats', [App\Http\Controllers\Api\PlayerStatsController::class, 'getMatchStats']);

    // Team Stats
    Route::get('/teams/{team}/stats/overview', [App\Http\Controllers\Api\TeamStatsController::class, 'getOverview']);
    Route::get('/teams/{team}/stats/analysis', [App\Http\Controllers\Api\TeamStatsController::class, 'getAnalysis']);

    // User & Team Resources (accessible by authenticated users, controlled by policies)
    Route::apiResource('users', UserController::class)->except(['index', 'store', 'destroy']);
    Route::apiResource('teams', TeamController::class)->except(['index', 'store', 'destroy']);

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

    // Match Recap Import
    Route::post('/matches/{encounter}/recap/prepare', [App\Http\Controllers\Api\MatchRecapController::class, 'prepareRecap']);
    Route::post('/matches/{encounter}/recap/import', [App\Http\Controllers\Api\MatchRecapController::class, 'importRecap']);
});
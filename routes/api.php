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

// Routes publiques
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);



// Route sous sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    // Profile
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/me/teams', [AuthController::class, 'myTeams']);
    Route::get('/me/dashboard', [AuthController::class, 'myDashboard']);
    Route::get('/me/matches', [AuthController::class, 'myMatches']);

    // Stats Joueurs
    Route::get('/players/{user}/stats/averages', [App\Http\Controllers\Api\PlayerStatsController::class, 'getAverages']);
    Route::get('/players/{user}/stats/historical/{stat}', [App\Http\Controllers\Api\PlayerStatsController::class, 'getHistorical'])->where('stat', '[a-zA-Z]+');
    Route::get('/players/{user}/match/{encounter}/stats', [App\Http\Controllers\Api\PlayerStatsController::class, 'getMatchStats']);

    // Stats Equipes
    Route::get('/teams/{team}/stats/overview', [App\Http\Controllers\Api\TeamStatsController::class, 'getOverview']);
    Route::get('/teams/{team}/stats/analysis', [App\Http\Controllers\Api\TeamStatsController::class, 'getAnalysis']);
    Route::get('/teams/{team}/stats/shooting', [App\Http\Controllers\Api\TeamStatsController::class, 'getShooting']);
    Route::get('/teams/{team}/stats/players', [App\Http\Controllers\Api\TeamStatsController::class, 'getPlayersStats']);
    Route::get('/teams/{team}/stats/periods', [App\Http\Controllers\Api\TeamStatsController::class, 'getPeriodStats']);

    // infos licenciés et equipes dispos pour le licencié connecté
    Route::apiResource('users', UserController::class)->except(['index', 'store', 'destroy']);
    Route::apiResource('teams', TeamController::class)->except(['index', 'store', 'destroy']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
});

// Routes Admin
Route::middleware(['auth:sanctum', 'can:access-admin-panel'])->group(function () {

    // Licenciés
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Roles
    Route::get('/user-types', [UserTypeController::class, 'index']);
    Route::get('/user-types/{id}', [UserTypeController::class, 'show']);
    Route::post('/user-types', [UserTypeController::class, 'store']);
    Route::put('/user-types/{id}', [UserTypeController::class, 'update']);
    Route::delete('/user-types/{id}', [UserTypeController::class, 'destroy']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Saisons
    Route::apiResource('seasons', SeasonController::class);

    // Equipes
    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{team}/players', [TeamController::class, 'addPlayer'])->name('teams.players.add');
    Route::delete('teams/{team}/players/{player}', [TeamController::class, 'removePlayer'])->name('teams.players.remove');
    Route::post('teams/{team}/coach', [TeamController::class, 'assignCoach'])->name('teams.coach.assign');

    // Evenements (Admin-only actions)
    Route::apiResource('events', EventController::class)->except(['index', 'show']);

    // Matchs
    Route::apiResource('encounters', EncounterController::class);
    Route::put('encounters/{encounter}/result', [EncounterController::class, 'updateResult'])->name('encounters.updateResult');
    Route::post('encounters/{encounter}/stats', [EncounterController::class, 'uploadStats'])->name('encounters.stats.upload');

    // Import stats macths
    Route::post('/matches/{encounter}/recap/prepare', [App\Http\Controllers\Api\MatchRecapController::class, 'prepareRecap']);
    Route::post('/matches/{encounter}/recap/import', [App\Http\Controllers\Api\MatchRecapController::class, 'importRecap']);
});

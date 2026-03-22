<?php

use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;


// User routes
Route::middleware('role:super_admin,general_manager')->group(function () {

    // List and detail
    Route::get('/users',         [UserController::class, 'index']);
    Route::get('/users/{user}',  [UserController::class, 'show']);

    // Super Admin only
    Route::middleware('role:super_admin')->group(function () {
        Route::post('/users',                   [UserController::class, 'store']);
        Route::put('/users/{user}',             [UserController::class, 'update']);
        Route::delete('/users/{user}',          [UserController::class, 'destroy']);
        Route::patch('/users/{user}/status',    [UserController::class, 'changeStatus']);
    });
});

// Reference endpoints
Route::middleware('role:super_admin,general_manager,project_manager')->group(function () {
    Route::get('/users/list/project-managers', [UserController::class, 'getProjectManagers']);
    Route::get('/users/list/team-leaders',     [UserController::class, 'getTeamLeaders']);
});

Route::middleware('role:super_admin,general_manager,project_manager,team_leader')->group(function () {
    Route::get('/users/list/employees', [UserController::class, 'getEmployees']);
});
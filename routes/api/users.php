<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

Route::middleware('permission:users.view')->group(function () {
    Route::get('/users',        [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
});

Route::middleware('permission:users.create')->group(function () {
    Route::post('/users', [UserController::class, 'store']);
});

Route::middleware('permission:users.update')->group(function () {
    Route::put('/users/{user}', [UserController::class, 'update']);
});

Route::middleware('permission:users.delete')->group(function () {
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});

Route::middleware('permission:users.change-status')->group(function () {
    Route::patch('/users/{user}/status', [UserController::class, 'changeStatus']);
});

Route::middleware('permission:users.view')->group(function () {
    Route::get('/users/list/project-managers', [UserController::class, 'getProjectManagers']);
    Route::get('/users/list/team-leaders',     [UserController::class, 'getTeamLeaders']);
    Route::get('/users/list/employees',        [UserController::class, 'getEmployees']);
});
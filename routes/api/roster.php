<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RosterAssignmentController;

Route::middleware('permission:roster.view')->group(function () {
    Route::get('/roster',                       [RosterAssignmentController::class, 'index']);
    Route::get('/roster/{id}',                  [RosterAssignmentController::class, 'show']);
    Route::get('/roster/user/{userId}',         [RosterAssignmentController::class, 'getUserRoster']);
    Route::get('/roster/user/{userId}/history', [RosterAssignmentController::class, 'getRosterHistory']);
    Route::get('/roster/shift/{shiftId}/users', [RosterAssignmentController::class, 'getUsersByShift']);
});

Route::middleware('permission:roster.assign')->group(function () {
    Route::post('/roster', [RosterAssignmentController::class, 'store']);
});
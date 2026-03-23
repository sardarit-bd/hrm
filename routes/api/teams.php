<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TeamController;

Route::middleware('permission:teams.view')->group(function () {
    Route::get('/teams',     [TeamController::class, 'index']);
    Route::get('/teams/my',  [TeamController::class, 'myTeams']);
    Route::get('/teams/{team}', [TeamController::class, 'show']);
    Route::get('/teams/project-assignments/{assignmentId}/members', [TeamController::class, 'getProjectMembers']);
});

Route::middleware('permission:teams.create')->group(function () {
    Route::post('/teams', [TeamController::class, 'store']);
});

Route::middleware('permission:teams.update')->group(function () {
    Route::put('/teams/{team}', [TeamController::class, 'update']);
});

Route::middleware('permission:teams.delete')->group(function () {
    Route::delete('/teams/{team}', [TeamController::class, 'destroy']);
});

Route::middleware('permission:teams.assign-member')->group(function () {
    Route::post('/teams/{team}/members',           [TeamController::class, 'addMember']);
    Route::delete('/teams/{team}/members/{user}',  [TeamController::class, 'removeMember']);
});

Route::middleware('permission:teams.assign-project')->group(function () {
    Route::post('/teams/{team}/projects', [TeamController::class, 'assignToProject']);
});

Route::middleware('permission:teams.assign-project-member')->group(function () {
    Route::post('/teams/project-assignments/{assignmentId}/members',            [TeamController::class, 'assignMemberToProject']);
    Route::delete('/teams/project-assignments/{assignmentId}/members/{userId}', [TeamController::class, 'releaseMemberFromProject']);
});
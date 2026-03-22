<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TeamController;

// Super Admin & GM - full access
Route::middleware('role:super_admin,general_manager')->group(function () {
    Route::get('/teams',            [TeamController::class, 'index']);
    Route::post('/teams',           [TeamController::class, 'store']);
    Route::get('/teams/{team}',     [TeamController::class, 'show']);
    Route::put('/teams/{team}',     [TeamController::class, 'update']);
    Route::delete('/teams/{team}',  [TeamController::class, 'destroy']);
});

// PM - manage team members and project assignments
Route::middleware('role:super_admin,general_manager,project_manager')->group(function () {
    Route::post('/teams/{team}/members',            [TeamController::class, 'addMember']);
    Route::delete('/teams/{team}/members/{user}',   [TeamController::class, 'removeMember']);
    Route::post('/teams/{team}/projects',           [TeamController::class, 'assignToProject']);
});

// Team Leader - assign members to projects
Route::middleware('role:super_admin,general_manager,project_manager,team_leader')->group(function () {
    Route::get('/teams/my',                                                     [TeamController::class, 'myTeams']);
    Route::post('/teams/project-assignments/{assignmentId}/members',            [TeamController::class, 'assignMemberToProject']);
    Route::delete('/teams/project-assignments/{assignmentId}/members/{userId}', [TeamController::class, 'releaseMemberFromProject']);
    Route::get('/teams/project-assignments/{assignmentId}/members',             [TeamController::class, 'getProjectMembers']);
});
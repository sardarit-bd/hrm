<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\MilestoneController;
use App\Http\Controllers\API\HourLogController;

// =================== Projects ===================
Route::middleware('role:super_admin,general_manager')->group(function () {
    Route::get('/projects',         [ProjectController::class, 'index']);
    Route::post('/projects',        [ProjectController::class, 'store']);
    Route::get('/projects/overdue', [ProjectController::class, 'getOverdue']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
});

Route::middleware('role:super_admin,general_manager,project_manager,team_leader,employee')->group(function () {
    Route::get('/projects/my',      [ProjectController::class, 'myProjects']);
    Route::get('/projects/ongoing', [ProjectController::class, 'getOngoing']);
    Route::get('/projects/{id}',    [ProjectController::class, 'show']);
});

Route::middleware('role:super_admin,general_manager,project_manager')->group(function () {
    Route::put('/projects/{id}',          [ProjectController::class, 'update']);
    Route::patch('/projects/{id}/status', [ProjectController::class, 'updateStatus']);
});

// =================== Milestones ===================
Route::middleware('role:super_admin,general_manager,project_manager,team_leader,employee')->group(function () {
    Route::get('/milestones',       [MilestoneController::class, 'index']);
    Route::get('/milestones/{id}',  [MilestoneController::class, 'show']);
});

Route::middleware('role:super_admin,general_manager,project_manager')->group(function () {
    Route::post('/milestones',                  [MilestoneController::class, 'store']);
    Route::put('/milestones/{id}',              [MilestoneController::class, 'update']);
    Route::delete('/milestones/{id}',           [MilestoneController::class, 'destroy']);
    Route::patch('/milestones/{id}/complete',   [MilestoneController::class, 'markAsCompleted']);
    Route::patch('/milestones/{id}/missed',     [MilestoneController::class, 'markAsMissed']);
    Route::get('/milestones/overdue',           [MilestoneController::class, 'getOverdue']);
});

// =================== Hour Logs ===================
Route::middleware('role:super_admin,general_manager,project_manager,team_leader,employee')->group(function () {
    Route::get('/hour-logs/my',     [HourLogController::class, 'myLogs']);
    Route::post('/hour-logs',       [HourLogController::class, 'store']);
    Route::get('/hour-logs/{id}',   [HourLogController::class, 'show']);
    Route::put('/hour-logs/{id}',   [HourLogController::class, 'update']);
    Route::delete('/hour-logs/{id}',[HourLogController::class, 'destroy']);
});

Route::middleware('role:super_admin,general_manager,project_manager,team_leader')->group(function () {
    Route::get('/hour-logs',                            [HourLogController::class, 'index']);
    Route::patch('/hour-logs/{id}/approve',             [HourLogController::class, 'approve']);
    Route::patch('/hour-logs/{id}/reject',              [HourLogController::class, 'reject']);
    Route::get('/hour-logs/project/{projectId}/summary',[HourLogController::class, 'projectSummary']);
});
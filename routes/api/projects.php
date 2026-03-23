<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\MilestoneController;
use App\Http\Controllers\API\HourLogController;

// Projects
Route::middleware('permission:projects.view')->group(function () {
    Route::get('/projects',         [ProjectController::class, 'index']);
    Route::get('/projects/my',      [ProjectController::class, 'myProjects']);
    Route::get('/projects/ongoing', [ProjectController::class, 'getOngoing']);
    Route::get('/projects/overdue', [ProjectController::class, 'getOverdue']);
    Route::get('/projects/{id}',    [ProjectController::class, 'show']);
});

Route::middleware('permission:projects.create')->group(function () {
    Route::post('/projects', [ProjectController::class, 'store']);
});

Route::middleware('permission:projects.update')->group(function () {
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
});

Route::middleware('permission:projects.change-status')->group(function () {
    Route::patch('/projects/{id}/status', [ProjectController::class, 'updateStatus']);
});

Route::middleware('permission:projects.delete')->group(function () {
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
});

// Milestones
Route::middleware('permission:milestones.view')->group(function () {
    Route::get('/milestones',      [MilestoneController::class, 'index']);
    Route::get('/milestones/{id}', [MilestoneController::class, 'show']);
});

Route::middleware('permission:milestones.create')->group(function () {
    Route::post('/milestones', [MilestoneController::class, 'store']);
});

Route::middleware('permission:milestones.update')->group(function () {
    Route::put('/milestones/{id}', [MilestoneController::class, 'update']);
});

Route::middleware('permission:milestones.delete')->group(function () {
    Route::delete('/milestones/{id}', [MilestoneController::class, 'destroy']);
});

Route::middleware('permission:milestones.complete')->group(function () {
    Route::patch('/milestones/{id}/complete', [MilestoneController::class, 'markAsCompleted']);
});

Route::middleware('permission:milestones.missed')->group(function () {
    Route::patch('/milestones/{id}/missed', [MilestoneController::class, 'markAsMissed']);
});

Route::middleware('permission:milestones.view')->group(function () {
    Route::get('/milestones/overdue', [MilestoneController::class, 'getOverdue']);
});

// Hour Logs
Route::middleware('permission:hour-logs.view')->group(function () {
    Route::get('/hour-logs',                             [HourLogController::class, 'index']);
    Route::get('/hour-logs/{id}',                        [HourLogController::class, 'show']);
    Route::get('/hour-logs/my',                          [HourLogController::class, 'myLogs']);
    Route::get('/hour-logs/project/{projectId}/summary', [HourLogController::class, 'projectSummary']);
});

Route::middleware('permission:hour-logs.create')->group(function () {
    Route::post('/hour-logs', [HourLogController::class, 'store']);
});

Route::middleware('permission:hour-logs.update')->group(function () {
    Route::put('/hour-logs/{id}', [HourLogController::class, 'update']);
});

Route::middleware('permission:hour-logs.delete')->group(function () {
    Route::delete('/hour-logs/{id}', [HourLogController::class, 'destroy']);
});

Route::middleware('permission:hour-logs.approve')->group(function () {
    Route::patch('/hour-logs/{id}/approve', [HourLogController::class, 'approve']);
    Route::patch('/hour-logs/{id}/reject',  [HourLogController::class, 'reject']);
});
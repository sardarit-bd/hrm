<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\LeaveTypeController;
use App\Http\Controllers\API\LeaveRequestController;

// Leave Types - Super Admin & GM only
Route::middleware('role:super_admin,general_manager')->group(function () {
    Route::get('/leave/types',              [LeaveTypeController::class, 'index']);
    Route::get('/leave/types/{leaveType}',  [LeaveTypeController::class, 'show']);
    Route::post('/leave/types',             [LeaveTypeController::class, 'store']);
    Route::put('/leave/types/{leaveType}',  [LeaveTypeController::class, 'update']);
    Route::delete('/leave/types/{leaveType}', [LeaveTypeController::class, 'destroy']);
});

// Leave Requests - All authenticated users
Route::middleware('role:super_admin,general_manager,project_manager,team_leader,employee')->group(function () {

    // Employee submits and views own requests
    Route::post('/leave/requests',      [LeaveRequestController::class, 'store']);
    Route::get('/leave/requests/my',    [LeaveRequestController::class, 'myRequests']);
    Route::get('/leave/requests/{id}',  [LeaveRequestController::class, 'show']);
});

// PM actions
Route::middleware('role:super_admin,general_manager,project_manager')->group(function () {
    Route::get('/leave/requests/pending/pm',        [LeaveRequestController::class, 'pendingForPm']);
    Route::post('/leave/requests/{id}/pm-action',   [LeaveRequestController::class, 'pmAction']);
});

// GM actions
Route::middleware('role:super_admin,general_manager')->group(function () {
    Route::get('/leave/requests',               [LeaveRequestController::class, 'index']);
    Route::get('/leave/requests/pending/gm',    [LeaveRequestController::class, 'pendingForGm']);
    Route::post('/leave/requests/{id}/gm-action', [LeaveRequestController::class, 'gmAction']);
});
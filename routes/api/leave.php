<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\LeaveTypeController;
use App\Http\Controllers\API\LeaveRequestController;

// Leave Types
Route::middleware('permission:leave-type.view')->group(function () {
    Route::get('/leave/types',             [LeaveTypeController::class, 'index']);
    Route::get('/leave/types/{leaveType}', [LeaveTypeController::class, 'show']);
});

Route::middleware('permission:leave-type.create')->group(function () {
    Route::post('/leave/types', [LeaveTypeController::class, 'store']);
});

Route::middleware('permission:leave-type.update')->group(function () {
    Route::put('/leave/types/{leaveType}', [LeaveTypeController::class, 'update']);
});

Route::middleware('permission:leave-type.delete')->group(function () {
    Route::delete('/leave/types/{leaveType}', [LeaveTypeController::class, 'destroy']);
});

// Leave Requests
Route::middleware('permission:leave-request.view')->group(function () {
    Route::get('/leave/requests',              [LeaveRequestController::class, 'index']);
    Route::get('/leave/requests/{id}',         [LeaveRequestController::class, 'show']);
    Route::get('/leave/requests/my',           [LeaveRequestController::class, 'myRequests']);
    Route::get('/leave/requests/pending/pm',   [LeaveRequestController::class, 'pendingForPm']);
    Route::get('/leave/requests/pending/gm',   [LeaveRequestController::class, 'pendingForGm']);
});

Route::middleware('permission:leave-request.create')->group(function () {
    Route::post('/leave/requests', [LeaveRequestController::class, 'store']);
});

Route::middleware('permission:leave-request.approve-pm')->group(function () {
    Route::post('/leave/requests/{id}/pm-action', [LeaveRequestController::class, 'pmAction']);
});

Route::middleware('permission:leave-request.approve-gm')->group(function () {
    Route::post('/leave/requests/{id}/gm-action', [LeaveRequestController::class, 'gmAction']);
});
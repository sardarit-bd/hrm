<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AttendancePolicyController;

Route::middleware('permission:attendance-policy.view')->group(function () {
    Route::get('/attendance/policies',                       [AttendancePolicyController::class, 'index']);
    Route::get('/attendance/policies/{policy}',              [AttendancePolicyController::class, 'show']);
    Route::get('/attendance/policies/user/{userId}',         [AttendancePolicyController::class, 'getUserPolicy']);
    Route::get('/attendance/policies/user/{userId}/history', [AttendancePolicyController::class, 'getUserPolicyHistory']);
});

Route::middleware('permission:attendance-policy.create')->group(function () {
    Route::post('/attendance/policies', [AttendancePolicyController::class, 'store']);
});

Route::middleware('permission:attendance-policy.update')->group(function () {
    Route::put('/attendance/policies/{policy}', [AttendancePolicyController::class, 'update']);
});

Route::middleware('permission:attendance-policy.delete')->group(function () {
    Route::delete('/attendance/policies/{policy}', [AttendancePolicyController::class, 'destroy']);
});

Route::middleware('permission:attendance-policy.assign')->group(function () {
    Route::post('/attendance/policies/assign', [AttendancePolicyController::class, 'assign']);
});
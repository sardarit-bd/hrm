<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AttendancePolicyController;

Route::middleware('role:super_admin,general_manager')->group(function () {

    // Policy CRUD
    Route::get('/attendance/policies',                              [AttendancePolicyController::class, 'index']);
    Route::get('/attendance/policies/{policy}',                     [AttendancePolicyController::class, 'show']);
    Route::post('/attendance/policies',                             [AttendancePolicyController::class, 'store']);
    Route::put('/attendance/policies/{policy}',                     [AttendancePolicyController::class, 'update']);
    Route::delete('/attendance/policies/{policy}',                  [AttendancePolicyController::class, 'destroy']);

    // Policy assignment
    Route::post('/attendance/policies/assign',                      [AttendancePolicyController::class, 'assign']);
    Route::get('/attendance/policies/user/{userId}',                [AttendancePolicyController::class, 'getUserPolicy']);
    Route::get('/attendance/policies/user/{userId}/history',        [AttendancePolicyController::class, 'getUserPolicyHistory']);
});
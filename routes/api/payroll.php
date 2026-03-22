<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PayrollController;

// All employees - view own payroll
Route::middleware('role:super_admin,general_manager,project_manager,team_leader,employee')->group(function () {
    Route::get('/payroll/my', [PayrollController::class, 'myPayroll']);
});

// GM & Super Admin - full payroll access
Route::middleware('role:super_admin,general_manager')->group(function () {
    Route::get('/payroll',                              [PayrollController::class, 'index']);
    Route::get('/payroll/{id}',                         [PayrollController::class, 'show']);
    Route::put('/payroll/{id}',                         [PayrollController::class, 'update']);
    Route::post('/payroll/generate',                    [PayrollController::class, 'generate']);
    Route::post('/payroll/generate/bulk',               [PayrollController::class, 'generateBulk']);
    Route::patch('/payroll/{id}/approve',               [PayrollController::class, 'approve']);
    Route::patch('/payroll/{id}/paid',                  [PayrollController::class, 'markAsPaid']);
    Route::get('/payroll/user/{userId}/month/{month}',  [PayrollController::class, 'getByUserAndMonth']);
    Route::get('/payroll/user/{userId}/quarterly',      [PayrollController::class, 'quarterlySummary']);
});
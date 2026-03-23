<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PayrollController;

Route::middleware('permission:payroll.view')->group(function () {
    Route::get('/payroll',                             [PayrollController::class, 'index']);
    Route::get('/payroll/my',                          [PayrollController::class, 'myPayroll']);
    Route::get('/payroll/{id}',                        [PayrollController::class, 'show']);
    Route::get('/payroll/user/{userId}/month/{month}', [PayrollController::class, 'getByUserAndMonth']);
    Route::get('/payroll/user/{userId}/quarterly',     [PayrollController::class, 'quarterlySummary']);
});

Route::middleware('permission:payroll.generate')->group(function () {
    Route::post('/payroll/generate',       [PayrollController::class, 'generate']);
    Route::post('/payroll/generate/bulk',  [PayrollController::class, 'generateBulk']);
});

Route::middleware('permission:payroll.update')->group(function () {
    Route::put('/payroll/{id}', [PayrollController::class, 'update']);
});

Route::middleware('permission:payroll.approve')->group(function () {
    Route::patch('/payroll/{id}/approve', [PayrollController::class, 'approve']);
});

Route::middleware('permission:payroll.pay')->group(function () {
    Route::patch('/payroll/{id}/paid', [PayrollController::class, 'markAsPaid']);
});
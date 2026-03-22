<?php

use App\Http\Controllers\API\ShiftController;
use Illuminate\Support\Facades\Route;

// Shift routes
Route::middleware('role:super_admin')->group(function () {
    Route::post('/shifts',          [ShiftController::class, 'store']);
    Route::put('/shifts/{shift}',   [ShiftController::class, 'update']);
    Route::delete('/shifts/{shift}',[ShiftController::class, 'destroy']);
});

Route::middleware('role:super_admin,general_manager')->group(function () {
    Route::get('/shifts',               [ShiftController::class, 'index']);
    Route::get('/shifts/{shift}',       [ShiftController::class, 'show']);
    Route::get('/shifts/list/fixed',    [ShiftController::class, 'getFixedShifts']);
    Route::get('/shifts/list/rotating', [ShiftController::class, 'getRotatingShifts']);
});
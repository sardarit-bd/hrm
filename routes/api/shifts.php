<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ShiftController;

Route::middleware('permission:shifts.view')->group(function () {
    Route::get('/shifts',                [ShiftController::class, 'index']);
    Route::get('/shifts/{shift}',        [ShiftController::class, 'show']);
    Route::get('/shifts/list/fixed',     [ShiftController::class, 'getFixedShifts']);
    Route::get('/shifts/list/rotating',  [ShiftController::class, 'getRotatingShifts']);
});

Route::middleware('permission:shifts.create')->group(function () {
    Route::post('/shifts', [ShiftController::class, 'store']);
});

Route::middleware('permission:shifts.update')->group(function () {
    Route::put('/shifts/{shift}', [ShiftController::class, 'update']);
});

Route::middleware('permission:shifts.delete')->group(function () {
    Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy']);
});
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\HolidayController;

Route::middleware('permission:holidays.view')->group(function () {
    Route::get('/holidays',          [HolidayController::class, 'index']);
    Route::get('/holidays/upcoming', [HolidayController::class, 'upcoming']);
    Route::get('/holidays/{id}',     [HolidayController::class, 'show']);
});

Route::middleware('permission:holidays.create')->group(function () {
    Route::post('/holidays', [HolidayController::class, 'store']);
});

Route::middleware('permission:holidays.update')->group(function () {
    Route::put('/holidays/{id}', [HolidayController::class, 'update']);
});

Route::middleware('permission:holidays.delete')->group(function () {
    Route::delete('/holidays/{id}', [HolidayController::class, 'destroy']);
});
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\HolidayController;

// All authenticated users can view holidays
Route::middleware('role:super_admin,general_manager,project_manager,team_leader,employee')->group(function () {
    Route::get('/holidays',          [HolidayController::class, 'index']);
    Route::get('/holidays/upcoming', [HolidayController::class, 'upcoming']);
    Route::get('/holidays/{id}',     [HolidayController::class, 'show']);
});

// Super Admin & GM only - manage holidays
Route::middleware('role:super_admin,general_manager')->group(function () {
    Route::post('/holidays',        [HolidayController::class, 'store']);
    Route::put('/holidays/{id}',    [HolidayController::class, 'update']);
    Route::delete('/holidays/{id}', [HolidayController::class, 'destroy']);
});
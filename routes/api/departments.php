<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\DepartmentController;

Route::middleware('permission:users.view')->group(function () {
    Route::get('/departments',         [DepartmentController::class, 'index']);
    Route::get('/departments/active',  [DepartmentController::class, 'getActive']);
    Route::get('/departments/{id}',    [DepartmentController::class, 'show']);
});

Route::middleware('permission:users.create')->group(function () {
    Route::post('/departments', [DepartmentController::class, 'store']);
});

Route::middleware('permission:users.update')->group(function () {
    Route::put('/departments/{id}', [DepartmentController::class, 'update']);
});

Route::middleware('permission:users.delete')->group(function () {
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);
});
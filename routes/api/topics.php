<?php

use App\Http\Controllers\API\TopicController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:feedback.submit')->group(function () {
    Route::get('/topics/active', [TopicController::class, 'active']);
});

Route::middleware('permission:feedback.view')->group(function () {
    Route::get('/topics', [TopicController::class, 'index']);
    Route::get('/topics/{id}', [TopicController::class, 'show']);
});

Route::middleware('permission:feedback.view')->group(function () {
    Route::post('/topics', [TopicController::class, 'store']);
    Route::put('/topics/{id}', [TopicController::class, 'update']);
    Route::delete('/topics/{id}', [TopicController::class, 'destroy']);
});

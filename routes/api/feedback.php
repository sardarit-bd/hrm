<?php

use App\Http\Controllers\API\AnonymousFeedbackController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:feedback.submit')->group(function () {
    Route::post('/feedback', [AnonymousFeedbackController::class, 'store']);
});

Route::middleware('permission:feedback.view')->group(function () {
    Route::get('/feedback',                        [AnonymousFeedbackController::class, 'index']);
    Route::get('/feedback/summary/topic',          [AnonymousFeedbackController::class, 'summaryByTopic']);

    // Backward-compatible alias
    Route::get('/feedback/summary/category',       [AnonymousFeedbackController::class, 'summaryByTopic']);
});

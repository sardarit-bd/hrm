<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AnonymousFeedbackController;

Route::middleware('permission:feedback.submit')->group(function () {
    Route::post('/feedback', [AnonymousFeedbackController::class, 'store']);
});

Route::middleware('permission:feedback.view')->group(function () {
    Route::get('/feedback',                           [AnonymousFeedbackController::class, 'index']);
    Route::get('/feedback/summary/quarter/{quarter}', [AnonymousFeedbackController::class, 'summaryByQuarter']);
    Route::get('/feedback/summary/category',          [AnonymousFeedbackController::class, 'summaryByCategory']);
});
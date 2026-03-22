<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AnonymousFeedbackController;

// All authenticated users can submit feedback
Route::middleware('role:super_admin,general_manager,project_manager,team_leader,employee')->group(function () {
    Route::post('/feedback', [AnonymousFeedbackController::class, 'store']);
});

// GM & Super Admin can view feedback
Route::middleware('role:super_admin,general_manager')->group(function () {
    Route::get('/feedback',                             [AnonymousFeedbackController::class, 'index']);
    Route::get('/feedback/summary/quarter/{quarter}',   [AnonymousFeedbackController::class, 'summaryByQuarter']);
    Route::get('/feedback/summary/category',            [AnonymousFeedbackController::class, 'summaryByCategory']);
});
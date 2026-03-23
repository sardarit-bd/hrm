<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\NotificationController;

Route::middleware('permission:notifications.view')->group(function () {
    Route::get('/notifications',                [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count',   [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/mark-read',     [NotificationController::class, 'markRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::delete('/notifications/cleanup',     [NotificationController::class, 'cleanup']);
});
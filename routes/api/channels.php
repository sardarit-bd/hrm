<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ChannelController;

Route::middleware('permission:projects.view')->group(function () {
    Route::get('/channels',         [ChannelController::class, 'index']);
    Route::get('/channels/active',  [ChannelController::class, 'getActive']);
    Route::get('/channels/{id}',    [ChannelController::class, 'show']);
});

Route::middleware('permission:projects.create')->group(function () {
    Route::post('/channels', [ChannelController::class, 'store']);
});

Route::middleware('permission:projects.update')->group(function () {
    Route::put('/channels/{id}', [ChannelController::class, 'update']);
});

Route::middleware('permission:projects.delete')->group(function () {
    Route::delete('/channels/{id}', [ChannelController::class, 'destroy']);
});
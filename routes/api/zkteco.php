<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Zkteco\ZktecoSyncController;

Route::middleware('zkteco.sync')->group(function () {
    Route::post('/zkteco/sync', [ZktecoSyncController::class, 'sync']);
});
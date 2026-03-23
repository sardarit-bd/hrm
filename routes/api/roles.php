<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RoleController;

// View roles and permissions — Super Admin and GM
Route::middleware('permission:roles.view')->group(function () {
    Route::get('/roles',                            [RoleController::class, 'index']);
    Route::get('/roles/{id}',                       [RoleController::class, 'show']);
    Route::get('/permissions',                      [RoleController::class, 'permissions']);
    Route::get('/roles/user/{userId}/permissions',  [RoleController::class, 'userPermissions']);
});

// Manage permissions — Super Admin only
Route::middleware('permission:roles.assign-permission')->group(function () {
    Route::post('/roles/{id}/permissions/sync',     [RoleController::class, 'syncPermissions']);
    Route::post('/roles/{id}/permissions/give',     [RoleController::class, 'givePermission']);
    Route::delete('/roles/{id}/permissions/revoke', [RoleController::class, 'revokePermission']);
});

// Assign role to user — Super Admin only
Route::middleware('permission:roles.assign-user')->group(function () {
    Route::post('/roles/assign', [RoleController::class, 'assignRole']);
});
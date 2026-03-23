<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    require __DIR__ . '/api/auth.php';
    require __DIR__ . '/api/departments.php';
    require __DIR__ . '/api/users.php';
    require __DIR__ . '/api/shifts.php';
    require __DIR__ . '/api/roster.php';
    require __DIR__ . '/api/attendance.php';
    require __DIR__ . '/api/leave.php';
    require __DIR__ . '/api/projects.php';
    require __DIR__ . '/api/teams.php';
    require __DIR__ . '/api/payroll.php';
    require __DIR__ . '/api/feedback.php';
    require __DIR__ . '/api/notifications.php';
    require __DIR__ . '/api/holidays.php';
    require __DIR__ . '/api/zkteco.php';
    require __DIR__ . '/api/roles.php';
});
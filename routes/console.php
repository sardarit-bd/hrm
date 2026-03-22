<?php

use App\Jobs\DetectAbsentEmployeesJob;
use App\Jobs\GeneratePayrollJob;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Carbon;

// Absent detection — runs every night at 00:05
Schedule::job(DetectAbsentEmployeesJob::class)
    ->dailyAt('00:05')
    ->name('detect-absent-employees')
    ->withoutOverlapping();

// Payroll generation — runs on 1st of every month at 01:00
Schedule::call(function () {
    $lastMonth = Carbon::now()->subMonth()->format('Y-m');
    GeneratePayrollJob::dispatch($lastMonth);
})
    ->monthlyOn(1, '01:00')
    ->name('generate-payroll-monthly')
    ->withoutOverlapping();
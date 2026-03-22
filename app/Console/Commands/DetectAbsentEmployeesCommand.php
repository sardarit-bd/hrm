<?php

namespace App\Console\Commands;

use App\Jobs\DetectAbsentEmployeesJob;
use Illuminate\Console\Command;

class DetectAbsentEmployeesCommand extends Command
{
    protected $signature   = 'attendance:detect-absent
                                {--date= : Specific date to process (Y-m-d). Defaults to yesterday.}';

    protected $description = 'Detect and mark absent employees for a given date';

    public function handle(): int
    {
        $date = $this->option('date');

        if ($date) {
            $this->info("Running absent detection for date: {$date}");
        } else {
            $this->info('Running absent detection for yesterday: ' . now()->subDay()->toDateString());
        }

        DetectAbsentEmployeesJob::dispatch($date);

        $this->info('Job dispatched successfully.');

        return self::SUCCESS;
    }
}
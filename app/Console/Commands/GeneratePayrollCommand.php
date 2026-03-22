<?php

namespace App\Console\Commands;

use App\Jobs\GeneratePayrollJob;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GeneratePayrollCommand extends Command
{
    protected $signature   = 'payroll:generate
                                {--month= : Month to generate payroll for (Y-m). Defaults to last month.}
                                {--user=  : Specific user ID. Defaults to all employees.}';

    protected $description = 'Generate payroll for all or specific employee';

    public function handle(): int
    {
        $month  = $this->option('month')
            ?? Carbon::now()->subMonth()->format('Y-m');

        $userId = $this->option('user')
            ? (int) $this->option('user')
            : null;

        $this->info("Generating payroll for month: {$month}");

        if ($userId) {
            $this->info("For user ID: {$userId}");
        } else {
            $this->info('For all active employees');
        }

        GeneratePayrollJob::dispatch($month, $userId);

        $this->info('Payroll generation job dispatched successfully.');

        return self::SUCCESS;
    }
}
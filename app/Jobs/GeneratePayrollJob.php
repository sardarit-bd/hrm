<?php

namespace App\Jobs;

use App\Models\AttendanceRecord;
use App\Models\EmployeeSalary;
use App\Models\LateDeductionLog;
use App\Models\PayrollRecord;
use App\Models\User;
use App\Services\AttendancePolicyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeneratePayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 600;

    protected Carbon $payrollMonth;
    protected ?int   $userId;

    /**
     * @param string   $month  Format: Y-m (e.g. 2026-03)
     * @param int|null $userId Specific user or null for all employees
     */
    public function __construct(
        string $month,
        ?int $userId = null
    ) {
        $this->payrollMonth = Carbon::createFromFormat('Y-m', $month)
            ->startOfMonth();
        $this->userId       = $userId;
    }

    public function handle(
        AttendancePolicyService $policyService
    ): void {
        $monthString = $this->payrollMonth->format('Y-m');

        Log::info('[GeneratePayrollJob] Starting', [
            'month'   => $monthString,
            'user_id' => $this->userId ?? 'all',
        ]);

        // Get employees to process
        $employees = $this->getEmployees();

        $success = 0;
        $failed  = 0;
        $skipped = 0;

        foreach ($employees as $employee) {
            try {
                $result = $this->processEmployee(
                    $employee,
                    $policyService
                );

                match ($result) {
                    'success' => $success++,
                    'skipped' => $skipped++,
                    default   => $failed++,
                };

            } catch (\Throwable $e) {
                Log::error('[GeneratePayrollJob] Failed for employee', [
                    'user_id' => $employee->id,
                    'month'   => $monthString,
                    'error'   => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        Log::info('[GeneratePayrollJob] Complete', [
            'month'   => $monthString,
            'success' => $success,
            'skipped' => $skipped,
            'failed'  => $failed,
        ]);
    }

    /**
     * Get employees to process
     */
    private function getEmployees()
    {
        $query = User::where('status', 'active')
            ->whereIn('role', [
                'employee',
                'team_leader',
                'project_manager',
            ]);

        if ($this->userId) {
            $query->where('id', $this->userId);
        }

        return $query->get();
    }

    /**
     * Process payroll for single employee
     */
    private function processEmployee(
        User $employee,
        AttendancePolicyService $policyService
    ): string {
        $monthString = $this->payrollMonth->format('Y-m');
        $startDate   = $this->payrollMonth->copy()->startOfMonth()->toDateString();
        $endDate     = $this->payrollMonth->copy()->endOfMonth()->toDateString();

        // Skip if payroll already exists and is not draft
        $existing = PayrollRecord::where('user_id', $employee->id)
            ->whereYear('payroll_month', $this->payrollMonth->year)
            ->whereMonth('payroll_month', $this->payrollMonth->month)
            ->first();

        if ($existing && !$existing->isDraft()) {
            Log::info('[GeneratePayrollJob] Skipping - payroll already approved or paid', [
                'user_id' => $employee->id,
                'month'   => $monthString,
            ]);
            return 'skipped';
        }

        // Get active salary for this month
        $salary = EmployeeSalary::where('user_id', $employee->id)
            ->where('effective_from', '<=', $startDate)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $startDate);
            })
            ->latest('effective_from')
            ->first();

        if (!$salary) {
            Log::warning('[GeneratePayrollJob] No salary found', [
                'user_id' => $employee->id,
                'month'   => $monthString,
            ]);
            return 'skipped';
        }

        // Get active policy for this month
        $policy = $policyService->getActivePolicyForDate(
            $employee->id,
            $startDate
        );

        if (!$policy) {
            Log::warning('[GeneratePayrollJob] No policy found', [
                'user_id' => $employee->id,
                'month'   => $monthString,
            ]);
            return 'skipped';
        }

        // Get all attendance records for this month
        $attendance = AttendanceRecord::where('user_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Calculate attendance stats
        $totalWorkingDays = $attendance
            ->whereNotIn('status', ['weekend', 'holiday'])
            ->count();

        $daysPresent = $attendance
            ->whereIn('status', ['present', 'late'])
            ->count();

        $daysAbsent = $attendance
            ->where('status', 'absent')
            ->count();

        $lateCount = $attendance
            ->where('status', 'late')
            ->count();

        // Avoid division by zero
        if ($totalWorkingDays == 0) {
            Log::warning('[GeneratePayrollJob] No working days found', [
                'user_id' => $employee->id,
                'month'   => $monthString,
            ]);
            return 'skipped';
        }

        // Calculate daily rate
        $dailyRate = $salary->basic_salary / $totalWorkingDays;

        // Get carry forward from previous month
        $previousPayroll  = PayrollRecord::where('user_id', $employee->id)
            ->whereYear('payroll_month', $this->payrollMonth->copy()->subMonth()->year)
            ->whereMonth('payroll_month', $this->payrollMonth->copy()->subMonth()->month)
            ->first();

        $carryForwardIn  = $previousPayroll?->late_carry_forward_out ?? 0;

        // Total late count including carry forward
        $totalLateCount  = $lateCount + $carryForwardIn;

        // Calculate late threshold breaches
        $lateBreaches        = intdiv($totalLateCount, $policy->late_count_threshold);
        $lateCarryForwardOut = $totalLateCount % $policy->late_count_threshold;

        // Calculate deduction amounts
        $lateDeductionDays    = $lateBreaches * $policy->late_threshold_deduction_days;
        $lateDeductionAmount  = $lateDeductionDays * $dailyRate;
        $absentDeductionAmount = $daysAbsent * $dailyRate * $policy->absent_deduction_per_day;

        // Calculate gross and net salary
        $grossSalary = $salary->basic_salary;
        $netSalary   = max(
            0,
            $grossSalary - $lateDeductionAmount - $absentDeductionAmount
        );

        // Wrap everything in transaction
        DB::transaction(function () use (
            $employee,
            $policy,
            $salary,
            $existing,
            $monthString,
            $totalWorkingDays,
            $daysPresent,
            $daysAbsent,
            $lateCount,
            $carryForwardIn,
            $lateCarryForwardOut,
            $lateDeductionDays,
            $lateDeductionAmount,
            $absentDeductionAmount,
            $grossSalary,
            $netSalary,
            $attendance,
            $dailyRate,
            $startDate
        ) {
            // Create or update payroll record
            $payroll = PayrollRecord::updateOrCreate(
                [
                    'user_id'       => $employee->id,
                    'payroll_month' => $startDate,
                ],
                [
                    'attendance_policy_id'    => $policy->id,
                    'basic_salary'            => $salary->basic_salary,
                    'total_working_days'      => $totalWorkingDays,
                    'days_present'            => $daysPresent,
                    'days_absent'             => $daysAbsent,
                    'late_count'              => $lateCount,
                    'late_carry_forward_in'   => $carryForwardIn,
                    'late_carry_forward_out'  => $lateCarryForwardOut,
                    'late_deduction_days'     => $lateDeductionDays,
                    'late_deduction_amount'   => $lateDeductionAmount,
                    'absent_deduction_amount' => $absentDeductionAmount,
                    'grace_period_used'       => $policy->grace_period_minutes,
                    'gross_salary'            => $grossSalary,
                    'net_salary'              => $netSalary,
                    'payroll_status'          => 'draft',
                ]
            );

            // Delete existing deduction logs before recreating
            LateDeductionLog::where('payroll_record_id', $payroll->id)->delete();

            // Create itemized deduction logs for absent days
            foreach ($attendance->where('status', 'absent') as $record) {
                LateDeductionLog::create([
                    'payroll_record_id' => $payroll->id,
                    'deduction_type'    => 'absent',
                    'reference_date'    => $record->date,
                    'deduction_amount'  => $dailyRate * $policy->absent_deduction_per_day,
                    'note'              => 'Absent deduction for ' . $record->date->format('Y-m-d'),
                ]);
            }

            // Create itemized deduction log for late threshold breach
            if ($lateDeductionDays > 0) {
                LateDeductionLog::create([
                    'payroll_record_id' => $payroll->id,
                    'deduction_type'    => 'late',
                    'reference_date'    => now()->toDateString(),
                    'deduction_amount'  => $lateDeductionAmount,
                    'note'              => "Late threshold breach: {$lateDeductionDays} day(s) deducted",
                ]);
            }

            Log::info('[GeneratePayrollJob] Payroll generated', [
                'user_id'    => $employee->id,
                'month'      => $this->payrollMonth->format('Y-m'),
                'net_salary' => $netSalary,
            ]);
        });

        return 'success';
    }
}
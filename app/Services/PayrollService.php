<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\PayrollRecord;
use App\Repositories\PayrollRepository;
use App\Repositories\AttendancePolicyRepository;
use App\Services\AttendancePolicyService;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService extends BaseService
{
    public function __construct(
        PayrollRepository $repository,
        CacheService $cache,
        protected AttendancePolicyService $policyService
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get paginated payroll records
     */
    public function getPaginatedPayrolls(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    /**
     * Generate payroll for employee for a specific month
     */
    public function generatePayroll(
        int $userId,
        string $month
    ): Model {
        // Check if already generated
        if ($this->repository->existsForUserAndMonth($userId, $month)) {
            throw new \Exception(
                "Payroll already exists for this employee for {$month}"
            );
        }

        return $this->transaction(function () use ($userId, $month) {

            $date       = Carbon::createFromFormat('Y-m', $month);
            $year       = $date->year;
            $monthNum   = $date->month;
            $startDate  = $date->copy()->startOfMonth()->toDateString();
            $endDate    = $date->copy()->endOfMonth()->toDateString();

            // Get active policy for user
            $policy = $this->policyService->getActivePolicyForUser($userId);

            if (!$policy) {
                throw new \Exception(
                    'No active attendance policy found for this employee'
                );
            }

            // Get active salary
            $salary = \App\Models\EmployeeSalary::where('user_id', $userId)
                ->where('effective_from', '<=', $startDate)
                ->where(function ($q) use ($startDate) {
                    $q->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $startDate);
                })
                ->latest('effective_from')
                ->first();

            if (!$salary) {
                throw new \Exception(
                    'No active salary found for this employee'
                );
            }

            // Get attendance records for the month
            $attendance = AttendanceRecord::where('user_id', $userId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            // Calculate attendance stats
            $totalWorkingDays = $attendance->whereNotIn(
                'status', ['weekend', 'holiday']
            )->count();

            $daysPresent = $attendance->whereIn(
                'status', ['present', 'late']
            )->count();

            $daysAbsent = $attendance->where('status', 'absent')->count();

            $lateCount = $attendance->where('status', 'late')->count();

            // Get carry forward from previous month
            $previousPayroll  = $this->repository->getPreviousMonthPayroll(
                $userId,
                $month
            );
            $carryForwardIn   = $previousPayroll?->late_carry_forward_out ?? 0;

            // Calculate total late count including carry forward
            $totalLateCount   = $lateCount + $carryForwardIn;

            // Calculate late deduction
            $lateBreaches          = intdiv(
                $totalLateCount,
                $policy->late_count_threshold
            );
            $lateCarryForwardOut   = $totalLateCount % $policy->late_count_threshold;
            $dailySalary           = $salary->basic_salary / $totalWorkingDays;
            $lateDeductionDays     = $lateBreaches * $policy->late_threshold_deduction_days;
            $lateDeductionAmount   = $lateDeductionDays * $dailySalary;

            // Calculate absent deduction
            $absentDeductionAmount = $daysAbsent * $dailySalary * $policy->absent_deduction_per_day;

            // Calculate gross and net salary
            $grossSalary = $salary->basic_salary;
            $netSalary   = $grossSalary - $lateDeductionAmount - $absentDeductionAmount;
            $netSalary   = max(0, $netSalary); // Never negative

            // Create payroll record
            $payroll = $this->repository->create([
                'user_id'                 => $userId,
                'attendance_policy_id'    => $policy->id,
                'payroll_month'           => $startDate,
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
            ]);

            // Create deduction logs
            $this->createDeductionLogs(
                $payroll->id,
                $attendance,
                $lateDeductionDays,
                $dailySalary,
                $policy
            );

            // Invalidate cache
            $this->cache->forgetPayrollSummary($userId, $month);

            $this->logInfo('Payroll generated', [
                'user_id'       => $userId,
                'month'         => $month,
                'net_salary'    => $netSalary,
            ]);

            return $payroll->load([
                'user',
                'attendancePolicy',
                'lateDeductionLogs',
            ]);
        });
    }

    /**
     * Generate payroll for all active employees for a month
     */
    public function generateBulkPayroll(string $month): array
    {
        $users    = \App\Models\User::where('status', 'active')->get();
        $results  = ['success' => [], 'failed' => []];

        foreach ($users as $user) {
            try {
                $payroll            = $this->generatePayroll($user->id, $month);
                $results['success'][] = [
                    'user_id'    => $user->id,
                    'full_name'  => $user->full_name,
                    'net_salary' => $payroll->net_salary,
                ];
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'user_id'   => $user->id,
                    'full_name' => $user->full_name,
                    'reason'    => $e->getMessage(),
                ];
            }
        }

        $this->logInfo('Bulk payroll generated', [
            'month'   => $month,
            'success' => count($results['success']),
            'failed'  => count($results['failed']),
        ]);

        return $results;
    }

    /**
     * Approve payroll
     */
    public function approvePayroll(int $id, int $approverId): Model
    {
        $payroll = $this->repository->findOrFail($id);

        if (!$payroll->isDraft()) {
            throw new \Exception('Only draft payrolls can be approved');
        }

        $payroll->approve(\App\Models\User::findOrFail($approverId));

        $this->cache->forgetPayrollSummary(
            $payroll->user_id,
            $payroll->payroll_month->format('Y-m')
        );

        $this->logInfo('Payroll approved', [
            'payroll_id'  => $id,
            'approver_id' => $approverId,
        ]);

        return $payroll->refresh()->load(['user', 'approvedBy']);
    }

    /**
     * Mark payroll as paid
     */
    public function markAsPaid(int $id): Model
    {
        $payroll = $this->repository->findOrFail($id);

        if (!$payroll->isApproved()) {
            throw new \Exception('Only approved payrolls can be marked as paid');
        }

        $payroll->markAsPaid();

        $this->cache->forgetPayrollSummary(
            $payroll->user_id,
            $payroll->payroll_month->format('Y-m')
        );

        $this->logInfo('Payroll marked as paid', ['payroll_id' => $id]);

        return $payroll->refresh()->load(['user', 'approvedBy']);
    }

    /**
     * Get payroll by user and month
     */
    public function getByUserAndMonth(
        int $userId,
        string $month
    ): ?PayrollRecord {
        return $this->cache->rememberPayrollSummary(
            $userId,
            $month,
            fn() => $this->repository->getByUserAndMonth($userId, $month)
        );
    }

    /**
     * Get quarterly summary
     */
    public function getQuarterlySummary(
        int $userId,
        int $year,
        int $quarter
    ): array {
        $records = $this->repository->getQuarterlySummary(
            $userId,
            $year,
            $quarter
        );

        return [
            'records'              => $records,
            'total_gross_salary'   => $records->sum('gross_salary'),
            'total_net_salary'     => $records->sum('net_salary'),
            'total_deductions'     => $records->sum(
                fn($r) => $r->totalDeductions()
            ),
            'total_days_present'   => $records->sum('days_present'),
            'total_days_absent'    => $records->sum('days_absent'),
            'total_late_count'     => $records->sum('late_count'),
        ];
    }

    /**
     * Create itemized deduction logs
     */
    private function createDeductionLogs(
        int $payrollId,
        $attendance,
        float $lateDeductionDays,
        float $dailySalary,
        $policy
    ): void {
        // Log absent days
        foreach ($attendance->where('status', 'absent') as $record) {
            $this->repository->createDeductionLog([
                'payroll_record_id' => $payrollId,
                'deduction_type'    => 'absent',
                'reference_date'    => $record->date,
                'deduction_amount'  => $dailySalary * $policy->absent_deduction_per_day,
                'note'              => 'Absent deduction',
            ]);
        }

        // Log late deduction if any
        if ($lateDeductionDays > 0) {
            $this->repository->createDeductionLog([
                'payroll_record_id' => $payrollId,
                'deduction_type'    => 'late',
                'reference_date'    => now()->toDateString(),
                'deduction_amount'  => $lateDeductionDays * $dailySalary,
                'note'              => "Late threshold breach deduction: {$lateDeductionDays} day(s)",
            ]);
        }
    }
}
<?php

namespace App\Jobs;

use App\Models\AttendanceRecord;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AttendancePolicyService;
use App\Services\RosterAssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DetectAbsentEmployeesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;

    protected Carbon $date;

    /**
     * Accept optional date for manual runs
     * Defaults to yesterday
     */
    public function __construct(?string $date = null)
    {
        $this->date = $date
            ? Carbon::parse($date)
            : Carbon::yesterday();
    }

    public function handle(
        RosterAssignmentService $rosterService,
        AttendancePolicyService $policyService
    ): void {
        $dateString = $this->date->toDateString();

        Log::info('[DetectAbsentEmployeesJob] Starting for date: ' . $dateString);

        // Safety check — never process today or future dates
        if ($this->date->greaterThanOrEqualTo(Carbon::today())) {
            Log::warning('[DetectAbsentEmployeesJob] Cannot process today or future dates.');
            return;
        }

        // Get all active employees
        $employees = User::where('status', 'active')
            ->whereIn('role', [
                'employee',
                'team_leader',
                'project_manager',
            ])
            ->get();

        $marked  = 0;
        $skipped = 0;
        $onLeave = 0;

        foreach ($employees as $employee) {
            try {
                $result = $this->processEmployee(
                    $employee,
                    $dateString,
                    $rosterService,
                    $policyService
                );

                match ($result) {
                    'marked'   => $marked++,
                    'on_leave' => $onLeave++,
                    default    => $skipped++,
                };

            } catch (\Throwable $e) {
                Log::error('[DetectAbsentEmployeesJob] Failed for employee', [
                    'user_id' => $employee->id,
                    'date'    => $dateString,
                    'error'   => $e->getMessage(),
                ]);
                $skipped++;
            }
        }

        Log::info('[DetectAbsentEmployeesJob] Complete', [
            'date'    => $dateString,
            'marked'  => $marked,
            'on_leave'=> $onLeave,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Process a single employee for the given date
     * Returns: marked | on_leave | skipped
     */
    private function processEmployee(
        User $employee,
        string $date,
        RosterAssignmentService $rosterService,
        AttendancePolicyService $policyService
    ): string {
        // Skip if employee joined after this date
        if ($employee->joining_date->gt(Carbon::parse($date))) {
            return 'skipped';
        }

        // Get active roster for this date
        $roster = $rosterService->getActiveRosterForDate(
            $employee->id,
            $date
        );

        // Skip if no roster assigned
        if (!$roster) {
            Log::warning('[DetectAbsentEmployeesJob] No roster found', [
                'user_id' => $employee->id,
                'date'    => $date,
            ]);
            return 'skipped';
        }

        // Skip if weekend for this employee
        $dayName = strtolower(Carbon::parse($date)->format('l'));
        if (in_array($dayName, $roster->weekend_days ?? [])) {
            return 'skipped';
        }

        // Skip if public holiday
        if ($this->isHoliday($date)) {
            return 'skipped';
        }

        // Skip if attendance record already exists
        // (present, late, half_day, on_leave, etc.)
        $exists = AttendanceRecord::where('user_id', $employee->id)
            ->where('date', $date)
            ->exists();

        if ($exists) {
            return 'skipped';
        }

        // Get active policy for this date
        $policy = $policyService->getActivePolicyForDate(
            $employee->id,
            $date
        );

        // Check if employee was on approved leave
        if ($this->isOnApprovedLeave($employee->id, $date)) {
            AttendanceRecord::create([
                'user_id'                => $employee->id,
                'roster_assignment_id'   => $roster->id,
                'policy_id'              => $policy?->id ?? $this->getDefaultPolicyId(),
                'date'                   => $date,
                'check_in'               => null,
                'check_out'              => null,
                'expected_check_in'      => $roster->shift->start_time,
                'expected_check_out'     => $roster->shift->end_time,
                'working_hours'          => 0,
                'late_minutes'           => 0,
                'overtime_minutes'       => 0,
                'is_within_grace_period' => false,
                'status'                 => 'on_leave',
            ]);

            return 'on_leave';
        }

        // No punch and no leave — mark as absent
        AttendanceRecord::create([
            'user_id'                => $employee->id,
            'roster_assignment_id'   => $roster->id,
            'policy_id'              => $policy?->id ?? $this->getDefaultPolicyId(),
            'date'                   => $date,
            'check_in'               => null,
            'check_out'              => null,
            'expected_check_in'      => $roster->shift->start_time,
            'expected_check_out'     => $roster->shift->end_time,
            'working_hours'          => 0,
            'late_minutes'           => 0,
            'overtime_minutes'       => 0,
            'is_within_grace_period' => false,
            'status'                 => 'absent',
        ]);

        return 'marked';
    }

    /**
     * Check if date is a public holiday
     */
    private function isHoliday(string $date): bool
    {
        return Holiday::where('date', $date)->exists();
    }

    /**
     * Check if employee is on approved leave for this date
     */
    private function isOnApprovedLeave(int $userId, string $date): bool
    {
        return LeaveRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->where('from_date', '<=', $date)
            ->where('to_date', '>=', $date)
            ->exists();
    }

    /**
     * Fallback policy ID if no policy assigned
     * Uses first available policy
     */
    private function getDefaultPolicyId(): int
    {
        return \App\Models\AttendancePolicy::first()?->id ?? 1;
    }
}
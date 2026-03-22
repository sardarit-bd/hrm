<?php

namespace App\Jobs;

use App\Models\AttendanceRecord;
use App\Models\Holiday;
use App\Models\User;
use App\Models\ZkPunchLog;
use App\Services\AttendancePolicyService;
use App\Services\RosterAssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessZktecoPunchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;

    public function __construct() {}

    public function handle(
        RosterAssignmentService $rosterService,
        AttendancePolicyService $policyService
    ): void {
        Log::info('[ProcessZktecoPunchesJob] Starting...');

        // Get all unprocessed punches
        // Group by employee_code + date
        // so we process one full day at a time
        $unprocessed = ZkPunchLog::where('is_processed', false)
            ->orderBy('punch_time')
            ->get()
            ->groupBy(function ($punch) {
                $date = Carbon::parse($punch->punch_time)->toDateString();
                return $punch->employee_code . '_' . $date;
            });

        if ($unprocessed->isEmpty()) {
            Log::info('[ProcessZktecoPunchesJob] No unprocessed punches.');
            return;
        }

        Log::info('[ProcessZktecoPunchesJob] Processing groups: ' . $unprocessed->count());

        foreach ($unprocessed as $key => $punches) {
            try {
                [$employeeCode, $date] = explode('_', $key, 2);

                // Find user by employee code
                $user = User::where('employee_code', $employeeCode)->first();

                if (!$user) {
                    Log::warning('[ProcessZktecoPunchesJob] User not found', [
                        'employee_code' => $employeeCode,
                    ]);

                    // Mark as processed to avoid infinite reprocessing
                    $punches->each->markAsProcessed();
                    continue;
                }

                // Get roster for this date
                $roster = $rosterService->getActiveRosterForDate(
                    $user->id,
                    $date
                );

                if (!$roster) {
                    Log::warning('[ProcessZktecoPunchesJob] No roster found', [
                        'user_id' => $user->id,
                        'date'    => $date,
                    ]);
                    $punches->each->markAsProcessed();
                    continue;
                }

                // Get policy for this date
                $policy = $policyService->getActivePolicyForDate(
                    $user->id,
                    $date
                );

                if (!$policy) {
                    Log::warning('[ProcessZktecoPunchesJob] No policy found', [
                        'user_id' => $user->id,
                        'date'    => $date,
                    ]);
                    $punches->each->markAsProcessed();
                    continue;
                }

                // Process this employee day
                $this->processEmployeeDay(
                    $user,
                    $date,
                    $punches,
                    $roster,
                    $policy
                );

                // Mark all punches for this group as processed
                $punches->each->markAsProcessed();

                Log::info('[ProcessZktecoPunchesJob] Processed', [
                    'user_id' => $user->id,
                    'date'    => $date,
                ]);

            } catch (\Throwable $e) {
                Log::error('[ProcessZktecoPunchesJob] Failed for ' . $key, [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('[ProcessZktecoPunchesJob] Complete.');
    }

    /**
     * Process attendance for one employee on one day
     */
    private function processEmployeeDay(
        User $user,
        string $date,
        $punches,
        $roster,
        $policy
    ): void {
        // Check if weekend
        $dayName = strtolower(Carbon::parse($date)->format('l'));

        if (in_array($dayName, $roster->weekend_days ?? [])) {
            $this->upsertAttendance($user->id, $date, $roster, $policy, [
                'status'                 => 'weekend',
                'check_in'               => null,
                'check_out'              => null,
                'working_hours'          => 0,
                'late_minutes'           => 0,
                'overtime_minutes'       => 0,
                'is_within_grace_period' => false,
            ]);
            return;
        }

        // Check if holiday
        if ($this->isHoliday($date)) {
            $this->upsertAttendance($user->id, $date, $roster, $policy, [
                'status'                 => 'holiday',
                'check_in'               => null,
                'check_out'              => null,
                'working_hours'          => 0,
                'late_minutes'           => 0,
                'overtime_minutes'       => 0,
                'is_within_grace_period' => false,
            ]);
            return;
        }

        // Get entry punch — first entry of the day
        $entryPunch = $punches
            ->where('punch_type', 'entry')
            ->sortBy('punch_time')
            ->first();

        // Get exit punch — last exit of the day
        $exitPunch = $punches
            ->where('punch_type', 'exit')
            ->sortByDesc('punch_time')
            ->first();

        // No entry punch means absent
        if (!$entryPunch) {
            $this->upsertAttendance($user->id, $date, $roster, $policy, [
                'status'                 => 'absent',
                'check_in'               => null,
                'check_out'              => null,
                'working_hours'          => 0,
                'late_minutes'           => 0,
                'overtime_minutes'       => 0,
                'is_within_grace_period' => false,
            ]);
            return;
        }

        // Calculate late minutes
        $shiftStart    = Carbon::parse($date . ' ' . $roster->shift->start_time);
        $checkIn       = Carbon::parse($entryPunch->punch_time);
        $graceEnd      = $shiftStart->copy()->addMinutes(
            $policy->grace_period_minutes
        );

        $lateMinutes   = 0;
        $isWithinGrace = false;

        if ($checkIn->gt($graceEnd)) {
            // Late beyond grace period
            $lateMinutes = $checkIn->diffInMinutes($shiftStart);
        } elseif ($checkIn->gt($shiftStart)) {
            // Within grace period
            $isWithinGrace = true;
        }

        // Calculate working hours and overtime
        $workingHours    = 0;
        $overtimeMinutes = 0;
        $checkOut        = null;

        if ($exitPunch) {
            $checkOut     = Carbon::parse($exitPunch->punch_time);
            $workingHours = round(
                $checkIn->diffInMinutes($checkOut) / 60,
                2
            );

            $shiftEnd = Carbon::parse(
                $date . ' ' . $roster->shift->end_time
            );

            // Handle cross midnight shifts
            if ($roster->shift->cross_midnight) {
                $shiftEnd->addDay();
            }

            if ($checkOut->gt($shiftEnd)) {
                $overtimeMinutes = $checkOut->diffInMinutes($shiftEnd);
            }
        }

        // Determine final status
        $status = $this->determineStatus(
            $workingHours,
            $lateMinutes,
            $policy
        );

        $this->upsertAttendance($user->id, $date, $roster, $policy, [
            'status'                 => $status,
            'check_in'               => $entryPunch->punch_time,
            'check_out'              => $exitPunch?->punch_time,
            'working_hours'          => $workingHours,
            'late_minutes'           => $lateMinutes,
            'overtime_minutes'       => $overtimeMinutes,
            'is_within_grace_period' => $isWithinGrace,
        ]);
    }

    /**
     * Determine attendance status based on working hours and late minutes
     */
    private function determineStatus(
        float $workingHours,
        int $lateMinutes,
        $policy
    ): string {
        if ($workingHours == 0) {
            return 'absent';
        }

        if ($workingHours < $policy->half_day_threshold_hours) {
            return 'half_day';
        }

        if ($lateMinutes > 0) {
            return 'late';
        }

        return 'present';
    }

    /**
     * Create or update attendance record
     * Uses updateOrCreate so reprocessing
     * updates existing record with corrected data
     */
    private function upsertAttendance(
        int $userId,
        string $date,
        $roster,
        $policy,
        array $data
    ): void {
        AttendanceRecord::updateOrCreate(
            [
                // Unique per employee per day
                'user_id' => $userId,
                'date'    => $date,
            ],
            array_merge($data, [
                'roster_assignment_id' => $roster->id,
                'policy_id'            => $policy->id,
                'expected_check_in'    => $roster->shift->start_time,
                'expected_check_out'   => $roster->shift->end_time,
            ])
        );
    }

    /**
     * Check if date is a holiday
     */
    private function isHoliday(string $date): bool
    {
        return Holiday::where('date', $date)->exists();
    }
}
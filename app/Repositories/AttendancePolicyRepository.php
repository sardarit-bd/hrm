<?php

namespace App\Repositories;

use App\Models\AttendancePolicy;
use App\Models\EmployeePolicyAssignment;
use Illuminate\Database\Eloquent\Collection;

class AttendancePolicyRepository extends BaseRepository
{
    public function __construct(AttendancePolicy $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active policies
     */
    public function getActivePolicies(): Collection
    {
        return $this->model
            ->whereNull('effective_to')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get active policy for user on a specific date
     */
    public function getActivePolicyForUser(
        int $userId,
        string $date
    ): ?AttendancePolicy {
        $assignment = EmployeePolicyAssignment::where('user_id', $userId)
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            })
            ->latest('effective_from')
            ->first();

        return $assignment?->attendancePolicy;
    }

    /**
     * Get current active policy for user
     */
    public function getCurrentPolicyForUser(int $userId): ?AttendancePolicy
    {
        $assignment = EmployeePolicyAssignment::where('user_id', $userId)
            ->whereNull('effective_to')
            ->latest('effective_from')
            ->first();

        return $assignment?->attendancePolicy;
    }

    /**
     * Close current active policy assignment for user
     */
    public function closeCurrentAssignment(
        int $userId,
        string $effectiveTo
    ): bool {
        return EmployeePolicyAssignment::where('user_id', $userId)
            ->whereNull('effective_to')
            ->update(['effective_to' => $effectiveTo]);
    }

    /**
     * Create new policy assignment
     */
    public function createAssignment(array $data): EmployeePolicyAssignment
    {
        return EmployeePolicyAssignment::create($data);
    }

    /**
     * Get policy assignment history for user
     */
    public function getAssignmentHistory(int $userId): Collection
    {
        return EmployeePolicyAssignment::with(['attendancePolicy'])
            ->where('user_id', $userId)
            ->orderByDesc('effective_from')
            ->get();
    }
}
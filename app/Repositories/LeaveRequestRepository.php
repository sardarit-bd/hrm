<?php

namespace App\Repositories;

use App\Models\LeaveRequest;
use App\Models\LeaveApproval;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveRequestRepository extends BaseRepository
{
    public function __construct(LeaveRequest $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated leave requests with filters
     */
    public function getPaginatedWithFilters(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model
            ->with(['user', 'leaveType', 'project', 'approvals.approver']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('from_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('to_date', '<=', $filters['to_date']);
        }

        return $query
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get pending leave requests for PM
     */
    public function getPendingForPm(int $projectId): Collection
    {
        return $this->model
            ->with(['user', 'leaveType'])
            ->where('status', 'pending_pm')
            ->where('project_id', $projectId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get pending leave requests for GM
     */
    public function getPendingForGm(): Collection
    {
        return $this->model
            ->with(['user', 'leaveType', 'project'])
            ->where('status', 'pending_gm')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Create leave approval record
     */
    public function createApproval(array $data): LeaveApproval
    {
        return LeaveApproval::create($data);
    }

    /**
     * Get leave requests for user in date range
     */
    public function getUserLeaveInRange(
        int $userId,
        string $from,
        string $to
    ): Collection {
        return $this->model
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('from_date', [$from, $to])
                    ->orWhereBetween('to_date', [$from, $to]);
            })
            ->get();
    }

    /**
     * Count approved leave days for user in year
     */
    public function countApprovedDaysInYear(
        int $userId,
        int $leaveTypeId,
        int $year
    ): int {
        return $this->model
            ->where('user_id', $userId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', 'approved')
            ->whereYear('from_date', $year)
            ->sum('total_days');
    }
}
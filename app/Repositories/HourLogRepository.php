<?php

namespace App\Repositories;

use App\Models\HourLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class HourLogRepository extends BaseRepository
{
    public function __construct(HourLog $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated hour logs with filters
     */
    public function getPaginatedWithFilters(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model
            ->with(['project', 'user', 'approvedBy']);

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('log_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('log_date', '<=', $filters['to_date']);
        }

        return $query
            ->orderByDesc('log_date')
            ->paginate($perPage);
    }

    /**
     * Get total hours logged for project
     */
    public function getTotalHoursByProject(int $projectId): float
    {
        return $this->model
            ->where('project_id', $projectId)
            ->where('status', 'approved')
            ->sum('hours_logged');
    }

    /**
     * Get total hours logged by user for project
     */
    public function getTotalHoursByUserAndProject(
        int $userId,
        int $projectId
    ): float {
        return $this->model
            ->where('user_id', $userId)
            ->where('project_id', $projectId)
            ->where('status', 'approved')
            ->sum('hours_logged');
    }

    /**
     * Get total hours by user in date range
     */
    public function getTotalHoursByUserInRange(
        int $userId,
        string $from,
        string $to
    ): float {
        return $this->model
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->whereBetween('log_date', [$from, $to])
            ->sum('hours_logged');
    }

    /**
     * Get pending hour logs for project
     */
    public function getPendingByProject(int $projectId): Collection
    {
        return $this->model
            ->with(['user'])
            ->where('project_id', $projectId)
            ->where('status', 'pending')
            ->orderByDesc('log_date')
            ->get();
    }
}
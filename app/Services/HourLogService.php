<?php

namespace App\Services;

use App\Models\HourLog;
use App\Repositories\HourLogRepository;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class HourLogService extends BaseService
{
    public function __construct(
        HourLogRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get paginated hour logs with filters
     */
    public function getPaginatedLogs(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    /**
     * Submit hour log
     */
    public function submitLog(array $data, int $userId): Model
    {
        $log = $this->repository->create([
            'project_id'   => $data['project_id'],
            'user_id'      => $userId,
            'log_date'     => $data['log_date'],
            'hours_logged' => $data['hours_logged'],
            'description'  => $data['description'] ?? null,
            'status'       => 'pending',
        ]);

        $this->logInfo('Hour log submitted', [
            'user_id'    => $userId,
            'project_id' => $data['project_id'],
            'hours'      => $data['hours_logged'],
        ]);

        return $log->load(['project', 'user']);
    }

    /**
     * Approve hour log
     */
    public function approve(int $id, int $approverId): Model
    {
        $log = $this->repository->findOrFail($id);

        if (!$log->isPending()) {
            throw new \Exception('Only pending hour logs can be approved');
        }

        $log->approve(
            \App\Models\User::findOrFail($approverId)
        );

        $this->logInfo('Hour log approved', [
            'log_id'      => $id,
            'approver_id' => $approverId,
        ]);

        return $log->refresh()->load(['project', 'user', 'approvedBy']);
    }

    /**
     * Reject hour log
     */
    public function reject(int $id, int $approverId): Model
    {
        $log = $this->repository->findOrFail($id);

        if (!$log->isPending()) {
            throw new \Exception('Only pending hour logs can be rejected');
        }

        $log->reject(
            \App\Models\User::findOrFail($approverId)
        );

        $this->logInfo('Hour log rejected', [
            'log_id'      => $id,
            'approver_id' => $approverId,
        ]);

        return $log->refresh()->load(['project', 'user', 'approvedBy']);
    }

    /**
     * Get total hours for project
     */
    public function getTotalHoursByProject(int $projectId): float
    {
        return $this->repository->getTotalHoursByProject($projectId);
    }

    /**
     * Get total hours by user in date range
     */
    public function getTotalHoursByUserInRange(
        int $userId,
        string $from,
        string $to
    ): float {
        return $this->repository->getTotalHoursByUserInRange(
            $userId,
            $from,
            $to
        );
    }

    /**
     * Get pending logs for project
     */
    public function getPendingByProject(int $projectId): array
    {
        $logs         = $this->repository->getPendingByProject($projectId);
        $totalPending = $logs->sum('hours_logged');

        return [
            'logs'          => $logs,
            'total_pending' => $totalPending,
        ];
    }
}
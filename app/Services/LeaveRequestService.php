<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Repositories\LeaveRequestRepository;
use App\Repositories\LeaveTypeRepository;
use App\Services\CacheService;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class LeaveRequestService extends BaseService
{
    public function __construct(
        LeaveRequestRepository $repository,
        CacheService $cache,
        protected LeaveTypeRepository $leaveTypeRepository,
        protected NotificationService $notificationService
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get paginated leave requests with filters
     */
    public function getPaginatedRequests(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    /**
     * Get pending requests for PM
     */
    public function getPendingForPm(int $projectId): Collection
    {
        return $this->repository->getPendingForPm($projectId);
    }

    /**
     * Get pending requests for GM
     */
    public function getPendingForGm(): Collection
    {
        return $this->repository->getPendingForGm();
    }

    /**
     * Submit leave request
     */
    public function submitRequest(array $data, int $userId): Model
    {
        return $this->transaction(function () use ($data, $userId) {

            // Calculate total days
            $from      = Carbon::parse($data['from_date']);
            $to        = Carbon::parse($data['to_date']);
            $totalDays = $from->diffInDays($to) + 1;

            // Check leave balance
            $this->validateLeaveBalance(
                $userId,
                $data['leave_type_id'],
                $totalDays
            );

            $request = $this->repository->create([
                'user_id'       => $userId,
                'leave_type_id' => $data['leave_type_id'],
                'project_id'    => $data['project_id'] ?? null,
                'from_date'     => $data['from_date'],
                'to_date'       => $data['to_date'],
                'total_days'    => $totalDays,
                'reason'        => $data['reason'] ?? null,
                'status'        => 'pending_pm',
            ]);

            $this->logInfo('Leave request submitted', [
                'user_id'    => $userId,
                'request_id' => $request->id,
                'total_days' => $totalDays,
            ]);

            $request->load(['user', 'leaveType', 'project']);

            $this->safeNotify(function () use ($request) {
                $this->notificationService->notifyPmForLeave($request);
            });

            return $request;
        });
    }

    /**
     * PM approves or rejects leave request
     */
    public function pmAction(
        int $requestId,
        int $approverId,
        string $action,
        ?string $remarks = null
    ): Model {
        return $this->transaction(function () use (
            $requestId,
            $approverId,
            $action,
            $remarks
        ) {
            $request = $this->repository->findOrFail($requestId);

            if ($request->status !== 'pending_pm') {
                throw new \Exception(
                    'Leave request is not pending PM approval'
                );
            }

            // Create approval record
            $this->repository->createApproval([
                'leave_request_id' => $requestId,
                'approver_id'      => $approverId,
                'approver_role'    => 'project_manager',
                'action'           => $action,
                'remarks'          => $remarks,
                'acted_at'         => now(),
            ]);

            // Update status
            $newStatus = $action === 'approved' ? 'pending_gm' : 'rejected';
            $this->repository->update($requestId, ['status' => $newStatus]);

            $request->refresh();

            $this->logInfo('PM acted on leave request', [
                'request_id'  => $requestId,
                'approver_id' => $approverId,
                'action'      => $action,
                'new_status'  => $newStatus,
            ]);

            $request->load(['user', 'leaveType', 'project', 'approvals.approver']);

            $this->safeNotify(function () use ($request, $action, $approverId) {
                if ($action === 'approved') {
                    $this->notificationService->notifyGmForLeave($request, $approverId);
                    return;
                }

                $this->notificationService->notifyEmployeeLeaveUpdated(
                    $request,
                    'rejected',
                    $approverId
                );
            });

            return $request;
        });
    }

    /**
     * GM approves or rejects leave request
     */
    public function gmAction(
        int $requestId,
        int $approverId,
        string $action,
        ?string $remarks = null
    ): Model {
        return $this->transaction(function () use (
            $requestId,
            $approverId,
            $action,
            $remarks
        ) {
            $request = $this->repository->findOrFail($requestId);

            if ($request->status !== 'pending_gm') {
                throw new \Exception(
                    'Leave request is not pending GM approval'
                );
            }

            // Create approval record
            $this->repository->createApproval([
                'leave_request_id' => $requestId,
                'approver_id'      => $approverId,
                'approver_role'    => 'general_manager',
                'action'           => $action,
                'remarks'          => $remarks,
                'acted_at'         => now(),
            ]);

            // Update status
            $newStatus = $action === 'approved' ? 'approved' : 'rejected';
            $this->repository->update($requestId, ['status' => $newStatus]);

            $request->refresh();

            $this->logInfo('GM acted on leave request', [
                'request_id'  => $requestId,
                'approver_id' => $approverId,
                'action'      => $action,
                'new_status'  => $newStatus,
            ]);

            $request->load(['user', 'leaveType', 'project', 'approvals.approver']);

            $this->safeNotify(function () use ($request, $action, $approverId) {
                $this->notificationService->notifyEmployeeLeaveUpdated(
                    $request,
                    $action,
                    $approverId
                );
            });

            return $request;
        });
    }

    /**
     * Validate leave balance
     */
    private function validateLeaveBalance(
        int $userId,
        int $leaveTypeId,
        int $requestedDays
    ): void {
        $leaveType = $this->leaveTypeRepository->findOrFail($leaveTypeId);

        $usedDays = $this->repository->countApprovedDaysInYear(
            $userId,
            $leaveTypeId,
            now()->year
        );

        $remainingDays = $leaveType->max_days_per_year - $usedDays;

        if ($requestedDays > $remainingDays) {
            throw new \Exception(
                "Insufficient leave balance. Remaining: {$remainingDays} days"
            );
        }
    }

    /**
     * Get leave requests for user in date range
     */
    public function getUserLeaveInRange(
        int $userId,
        string $from,
        string $to
    ): Collection {
        return $this->repository->getUserLeaveInRange($userId, $from, $to);
    }

    /**
     * Notification should not block core leave workflow.
     */
    private function safeNotify(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            $this->logWarning('Notification dispatch failed during leave workflow', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}

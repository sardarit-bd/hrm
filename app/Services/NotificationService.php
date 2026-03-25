<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Repositories\NotificationRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService extends BaseService
{
    public function __construct(
        NotificationRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get paginated notifications for user
     */
    public function getForUser(
        int $userId,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->repository->getPaginatedForUser(
            $userId,
            $filters,
            $perPage
        );
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->cache->remember(
            "notifications.unread.{$userId}",
            fn() => $this->repository->getUnreadCount($userId),
            300 // 5 minutes
        );
    }

    /**
     * Mark specific notifications as read
     */
    public function markAsRead(int $userId, array $ids): int
    {
        $count = $this->repository->markAsRead($userId, $ids);
        $this->cache->forget("notifications.unread.{$userId}");
        return $count;
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(int $userId): int
    {
        $count = $this->repository->markAllAsRead($userId);
        $this->cache->forget("notifications.unread.{$userId}");
        return $count;
    }

    /**
     * Send notification to single user
     */
    public function send(
        int $userId,
        string $title,
        string $message,
        string $type,
        array $options = []
    ): Model {
        $notification = $this->repository->create([
            'user_id'        => $userId,
            'sender_user_id' => $options['sender_user_id'] ?? null,
            'sender_type'    => $options['sender_type'] ?? 'system',
            'title'          => $title,
            'message'        => $message,
            'type'           => $type,
            'delivery_type'  => $options['delivery_type'] ?? 'system',
            'module'         => $options['module'] ?? null,
            'entity_type'    => $options['entity_type'] ?? null,
            'entity_id'      => $options['entity_id'] ?? null,
            'workflow_step'  => $options['workflow_step'] ?? null,
            'workflow_stage' => $options['workflow_stage'] ?? null,
            'context'        => $options['context'] ?? null,
            'delivered_at'   => $options['delivered_at'] ?? now(),
            'is_read'        => false,
        ]);

        $this->cache->forget("notifications.unread.{$userId}");

        return $notification;
    }

    /**
     * Send notification to multiple users
     */
    public function sendToMultiple(
        array $userIds,
        string $title,
        string $message,
        string $type,
        array $options = []
    ): void {
        if (empty($userIds)) {
            return;
        }

        $this->repository->createForMultipleUsers($userIds, [
            'title'          => $title,
            'message'        => $message,
            'type'           => $type,
            'sender_user_id' => $options['sender_user_id'] ?? null,
            'sender_type'    => $options['sender_type'] ?? 'system',
            'delivery_type'  => $options['delivery_type'] ?? 'system',
            'module'         => $options['module'] ?? null,
            'entity_type'    => $options['entity_type'] ?? null,
            'entity_id'      => $options['entity_id'] ?? null,
            'workflow_step'  => $options['workflow_step'] ?? null,
            'workflow_stage' => $options['workflow_stage'] ?? null,
            'context'        => $options['context'] ?? null,
            'delivered_at'   => $options['delivered_at'] ?? now(),
        ]);

        foreach ($userIds as $userId) {
            $this->cache->forget("notifications.unread.{$userId}");
        }
    }

    /**
     * Send custom notification from user to users
     */
    public function sendCustom(
        int $senderUserId,
        array $recipientIds,
        string $title,
        string $message,
        string $type,
        array $context = []
    ): void {
        $this->sendToMultiple(
            $recipientIds,
            $title,
            $message,
            $type,
            [
                'sender_user_id' => $senderUserId,
                'sender_type'    => 'user',
                'delivery_type'  => 'custom',
                'context'        => $context,
            ]
        );
    }

    /**
     * Leave workflow step 1: employee request -> PM
     */
    public function notifyPmForLeave(LeaveRequest $leaveRequest): void
    {
        $pmId = $leaveRequest->project?->project_manager_id;

        if (!$pmId) {
            return;
        }

        $this->send(
            $pmId,
            'Leave Request Pending PM Approval',
            "{$leaveRequest->user->full_name} submitted a leave request ({$leaveRequest->from_date->format('Y-m-d')} to {$leaveRequest->to_date->format('Y-m-d')}).",
            'leave',
            [
                'sender_type'    => 'system',
                'delivery_type'  => 'workflow',
                'module'         => 'leave_request',
                'entity_type'    => 'leave_request',
                'entity_id'      => $leaveRequest->id,
                'workflow_step'  => 1,
                'workflow_stage' => 'pending_pm',
                'context'        => [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id'      => $leaveRequest->user_id,
                    'project_id'       => $leaveRequest->project_id,
                ],
            ]
        );
    }

    /**
     * Leave workflow step 2: PM approved -> GM
     */
    public function notifyGmForLeave(LeaveRequest $leaveRequest, int $pmUserId): void
    {
        $gmIds = User::role('general_manager')->pluck('id')->all();

        $this->sendToMultiple(
            $gmIds,
            'Leave Request Pending GM Approval',
            "{$leaveRequest->user->full_name}'s leave request is approved by PM and waiting for GM decision.",
            'leave',
            [
                'sender_user_id' => $pmUserId,
                'sender_type'    => 'user',
                'delivery_type'  => 'workflow',
                'module'         => 'leave_request',
                'entity_type'    => 'leave_request',
                'entity_id'      => $leaveRequest->id,
                'workflow_step'  => 2,
                'workflow_stage' => 'pending_gm',
                'context'        => [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id'      => $leaveRequest->user_id,
                    'pm_user_id'       => $pmUserId,
                ],
            ]
        );
    }

    /**
     * Leave workflow final/update notification to employee
     */
    public function notifyEmployeeLeaveUpdated(
        LeaveRequest $leaveRequest,
        string $action,
        ?int $actorId = null
    ): void {
        $title = $action === 'approved'
            ? 'Leave Request Approved'
            : 'Leave Request Rejected';

        $message = $action === 'approved'
            ? 'Your leave request has been approved.'
            : 'Your leave request has been rejected.';

        $this->send(
            $leaveRequest->user_id,
            $title,
            $message,
            'leave',
            [
                'sender_user_id' => $actorId,
                'sender_type'    => $actorId ? 'user' : 'system',
                'delivery_type'  => 'workflow',
                'module'         => 'leave_request',
                'entity_type'    => 'leave_request',
                'entity_id'      => $leaveRequest->id,
                'workflow_step'  => 3,
                'workflow_stage' => $leaveRequest->status,
                'context'        => [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id'      => $leaveRequest->user_id,
                    'action'           => $action,
                ],
            ]
        );
    }

    /**
     * Delete old read notifications
     */
    public function cleanupOldNotifications(
        int $userId,
        int $days = 30
    ): int {
        return $this->repository->deleteOldRead($userId, $days);
    }
}

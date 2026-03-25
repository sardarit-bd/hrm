<?php

namespace App\Repositories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationRepository extends BaseRepository
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated notifications for user
     */
    public function getPaginatedForUser(
        int $userId,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model
            ->with(['sender:id,full_name,email'])
            ->where('user_id', $userId);

        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['sender_type'])) {
            $query->where('sender_type', $filters['sender_type']);
        }

        if (!empty($filters['delivery_type'])) {
            $query->where('delivery_type', $filters['delivery_type']);
        }

        return $query
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark notifications as read
     */
    public function markAsRead(int $userId, array $ids): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereIn('id', $ids)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(int $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Delete read notifications older than days
     */
    public function deleteOldRead(int $userId, int $days = 30): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('is_read', true)
            ->where('read_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Create notification for multiple users
     */
    public function createForMultipleUsers(
        array $userIds,
        array $payload
    ): void {
        $notifications = array_map(fn($userId) => [
            'user_id'    => $userId,
            'sender_user_id' => $payload['sender_user_id'] ?? null,
            'sender_type' => $payload['sender_type'] ?? 'system',
            'title'      => $payload['title'],
            'message'    => $payload['message'],
            'type'       => $payload['type'],
            'delivery_type' => $payload['delivery_type'] ?? 'system',
            'module'     => $payload['module'] ?? null,
            'entity_type' => $payload['entity_type'] ?? null,
            'entity_id'  => $payload['entity_id'] ?? null,
            'workflow_step' => $payload['workflow_step'] ?? null,
            'workflow_stage' => $payload['workflow_stage'] ?? null,
            'context'    => isset($payload['context']) ? json_encode($payload['context']) : null,
            'delivered_at' => $payload['delivered_at'] ?? now(),
            'is_read'    => false,
            'created_at' => now(),
        ], $userIds);

        $this->model->insert($notifications);
    }
}

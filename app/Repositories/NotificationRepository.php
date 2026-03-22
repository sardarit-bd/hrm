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
        $query = $this->model->where('user_id', $userId);

        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
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
        string $title,
        string $message,
        string $type
    ): void {
        $notifications = array_map(fn($userId) => [
            'user_id'    => $userId,
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'is_read'    => false,
            'created_at' => now(),
        ], $userIds);

        $this->model->insert($notifications);
    }
}
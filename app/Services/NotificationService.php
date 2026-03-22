<?php

namespace App\Services;

use App\Repositories\NotificationRepository;
use App\Services\CacheService;
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
        string $type
    ): Model {
        $notification = $this->repository->create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'is_read' => false,
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
        string $type
    ): void {
        $this->repository->createForMultipleUsers(
            $userIds,
            $title,
            $message,
            $type
        );

        foreach ($userIds as $userId) {
            $this->cache->forget("notifications.unread.{$userId}");
        }
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
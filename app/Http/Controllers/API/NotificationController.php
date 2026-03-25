<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\MarkReadRequest;
use App\Http\Requests\Notification\StoreCustomNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected NotificationService $notificationService
    ) {}

    #[OA\Get(
        path: '/api/v1/notifications',
        summary: 'Get authenticated user notifications with pagination',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(
                name: 'is_read',
                in: 'query',
                required: false,
                description: 'Filter by read status — true or false',
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'type',
                in: 'query',
                required: false,
                description: 'Filter by notification type e.g. leave, payroll',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15)
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notifications retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Notifications retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'title', type: 'string', example: 'Leave Approved'),
                                    new OA\Property(property: 'message', type: 'string'),
                                    new OA\Property(property: 'type', type: 'string', example: 'leave'),
                                    new OA\Property(property: 'is_read', type: 'boolean', example: false),
                                    new OA\Property(property: 'read_at', type: 'string', format: 'datetime', nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'datetime'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 30),
                                new OA\Property(property: 'last_page', type: 'integer', example: 2),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters       = $request->only(['is_read', 'type', 'sender_type', 'delivery_type']);
            $perPage       = $request->integer('per_page', 15);
            $notifications = $this->notificationService->getForUser(
                $request->auth_user->id,
                $filters,
                $perPage
            );
            return $this->paginatedResponse(
                NotificationResource::collection($notifications),
                'Notifications retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/notifications/unread-count',
        summary: 'Get unread notification count for authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Unread count retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Unread count retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'unread_count',
                                    type: 'integer',
                                    example: 5,
                                    description: 'Number of unread notifications'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $count = $this->notificationService->getUnreadCount($request->auth_user->id);
            return $this->successResponse(
                ['unread_count' => $count],
                'Unread count retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/notifications/mark-read',
        summary: 'Mark specific notifications as read',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['notification_ids'],
                properties: [
                    new OA\Property(
                        property: 'notification_ids',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [1, 2, 3],
                        description: 'Array of notification IDs to mark as read'
                    ),
                ]
            )
        ),
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notifications marked as read',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Notifications marked as read'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'marked_count',
                                    type: 'integer',
                                    example: 3,
                                    description: 'Number of notifications marked as read'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function markRead(MarkReadRequest $request): JsonResponse
    {
        try {
            $count = $this->notificationService->markAsRead(
                $request->auth_user->id,
                $request->notification_ids
            );
            return $this->successResponse(
                ['marked_count' => $count],
                'Notifications marked as read'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/notifications/mark-all-read',
        summary: 'Mark all notifications as read for authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All notifications marked as read',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'All notifications marked as read'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'marked_count',
                                    type: 'integer',
                                    example: 10,
                                    description: 'Total notifications marked as read'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function markAllRead(Request $request): JsonResponse
    {
        try {
            $count = $this->notificationService->markAllAsRead($request->auth_user->id);
            return $this->successResponse(
                ['marked_count' => $count],
                'All notifications marked as read'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/notifications/cleanup',
        summary: 'Delete old read notifications',
        description: 'Deletes read notifications older than the specified number of days',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(
                name: 'days',
                in: 'query',
                required: false,
                description: 'Delete read notifications older than this many days — defaults to 30',
                schema: new OA\Schema(type: 'integer', example: 30)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Old notifications cleaned up successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Old notifications cleaned up successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'deleted_count',
                                    type: 'integer',
                                    example: 15,
                                    description: 'Number of notifications deleted'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function cleanup(Request $request): JsonResponse
    {
        try {
            $days  = $request->integer('days', 30);
            $count = $this->notificationService->cleanupOldNotifications(
                $request->auth_user->id,
                $days
            );
            return $this->successResponse(
                ['deleted_count' => $count],
                'Old notifications cleaned up successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    public function sendCustom(StoreCustomNotificationRequest $request): JsonResponse
    {
        try {
            $payload = $request->validated();

            $this->notificationService->sendCustom(
                $request->auth_user->id,
                $payload['recipient_ids'],
                $payload['title'],
                $payload['message'],
                $payload['type'],
                $payload['context'] ?? []
            );

            return $this->successResponse(
                null,
                'Custom notification sent successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}

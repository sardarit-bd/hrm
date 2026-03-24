<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\ApproveLeaveRequest;
use App\Http\Requests\Leave\StoreLeaveRequestRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Services\LeaveRequestService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LeaveRequestController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected LeaveRequestService $leaveRequestService
    ) {}

    #[OA\Get(
        path: '/api/v1/leave/requests',
        summary: 'List all leave requests with filters and pagination',
        security: [['bearerAuth' => []]],
        tags: ['Leave Requests'],
        parameters: [
            new OA\Parameter(
                name: 'user_id',
                in: 'query',
                required: false,
                description: 'Filter by employee ID',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['pending_pm', 'pending_gm', 'approved', 'rejected']
                )
            ),
            new OA\Parameter(
                name: 'project_id',
                in: 'query',
                required: false,
                description: 'Filter by project ID',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'from_date',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'to_date',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
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
                description: 'Leave requests retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Leave requests retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'user', type: 'object'),
                                    new OA\Property(property: 'leave_type', type: 'object'),
                                    new OA\Property(property: 'from_date', type: 'string', format: 'date'),
                                    new OA\Property(property: 'to_date', type: 'string', format: 'date'),
                                    new OA\Property(property: 'total_days', type: 'integer', example: 3),
                                    new OA\Property(property: 'reason', type: 'string', nullable: true),
                                    new OA\Property(
                                        property: 'status',
                                        type: 'string',
                                        enum: ['pending_pm', 'pending_gm', 'approved', 'rejected']
                                    ),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 50),
                                new OA\Property(property: 'last_page', type: 'integer', example: 4),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters  = $request->only(['user_id', 'status', 'project_id', 'from_date', 'to_date']);
            $perPage  = $request->integer('per_page', 15);
            $requests = $this->leaveRequestService->getPaginatedRequests($filters, $perPage);

            return $this->paginatedResponse(
                LeaveRequestResource::collection($requests),
                'Leave requests retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/leave/requests',
        summary: 'Submit a new leave request',
        description: 'Employee submits a leave request. It automatically goes to the Project Manager for approval.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['leave_type_id', 'from_date', 'to_date'],
                properties: [
                    new OA\Property(
                        property: 'leave_type_id',
                        type: 'integer',
                        example: 1,
                        description: 'ID of the leave type'
                    ),
                    new OA\Property(
                        property: 'project_id',
                        type: 'integer',
                        example: 1,
                        nullable: true,
                        description: 'ID of the project — determines which PM receives the request'
                    ),
                    new OA\Property(
                        property: 'from_date',
                        type: 'string',
                        format: 'date',
                        example: '2026-04-01',
                        description: 'Leave start date — must be today or future'
                    ),
                    new OA\Property(
                        property: 'to_date',
                        type: 'string',
                        format: 'date',
                        example: '2026-04-03',
                        description: 'Leave end date — must be on or after from_date'
                    ),
                    new OA\Property(
                        property: 'reason',
                        type: 'string',
                        example: 'Personal reasons',
                        nullable: true,
                        description: 'Optional reason for leave'
                    ),
                ]
            )
        ),
        tags: ['Leave Requests'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Leave request submitted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Leave request submitted successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'total_days', type: 'integer', example: 3),
                                new OA\Property(property: 'status', type: 'string', example: 'pending_pm'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error or insufficient leave balance'),
        ]
    )]
    public function store(StoreLeaveRequestRequest $request): JsonResponse
    {
        try {
            $leaveRequest = $this->leaveRequestService->submitRequest(
                $request->validated(),
                $request->auth_user->id
            );
            return $this->createdResponse(
                new LeaveRequestResource($leaveRequest),
                'Leave request submitted successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/leave/requests/{id}',
        summary: 'Get leave request by ID with approval history',
        security: [['bearerAuth' => []]],
        tags: ['Leave Requests'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Leave request retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Leave request retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'user', type: 'object'),
                                new OA\Property(property: 'leave_type', type: 'object'),
                                new OA\Property(property: 'from_date', type: 'string', format: 'date'),
                                new OA\Property(property: 'to_date', type: 'string', format: 'date'),
                                new OA\Property(property: 'total_days', type: 'integer', example: 3),
                                new OA\Property(property: 'status', type: 'string', example: 'pending_pm'),
                                new OA\Property(
                                    property: 'approvals',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'approver', type: 'object'),
                                            new OA\Property(property: 'approver_role', type: 'string'),
                                            new OA\Property(property: 'action', type: 'string'),
                                            new OA\Property(property: 'remarks', type: 'string', nullable: true),
                                            new OA\Property(property: 'acted_at', type: 'string', format: 'datetime'),
                                        ]
                                    )
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Leave request not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $leaveRequest = $this->leaveRequestService->findOrFail(
                $id,
                ['*'],
                ['user', 'leaveType', 'project', 'approvals.approver']
            );
            return $this->successResponse(
                new LeaveRequestResource($leaveRequest),
                'Leave request retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Leave request not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/leave/requests/my',
        summary: 'Get authenticated employee own leave requests',
        security: [['bearerAuth' => []]],
        tags: ['Leave Requests'],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['pending_pm', 'pending_gm', 'approved', 'rejected']
                )
            ),
            new OA\Parameter(
                name: 'from_date',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'to_date',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Leave requests retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function myRequests(Request $request): JsonResponse
    {
        try {
            $filters            = $request->only(['status', 'from_date', 'to_date']);
            $filters['user_id'] = $request->auth_user->id;
            $perPage            = $request->integer('per_page', 15);
            $requests           = $this->leaveRequestService->getPaginatedRequests($filters, $perPage);
            return $this->paginatedResponse($requests, 'Leave requests retrieved successfully');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/leave/requests/pending/pm',
        summary: 'Get pending leave requests for Project Manager approval',
        security: [['bearerAuth' => []]],
        tags: ['Leave Requests'],
        parameters: [
            new OA\Parameter(
                name: 'project_id',
                in: 'query',
                required: true,
                description: 'ID of the project to filter pending requests',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pending leave requests retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Pending leave requests retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(type: 'object')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — PM and above only'),
        ]
    )]
    public function pendingForPm(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'project_id' => ['required', 'integer', 'exists:projects,id'],
            ]);
            $requests = $this->leaveRequestService->getPendingForPm($request->project_id);
            return $this->successResponse(
                LeaveRequestResource::collection($requests),
                'Pending leave requests retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/leave/requests/pending/gm',
        summary: 'Get all pending leave requests for General Manager approval',
        security: [['bearerAuth' => []]],
        tags: ['Leave Requests'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pending leave requests retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Pending leave requests retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(type: 'object')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
        ]
    )]
    public function pendingForGm(): JsonResponse
    {
        try {
            $requests = $this->leaveRequestService->getPendingForGm();
            return $this->successResponse(
                LeaveRequestResource::collection($requests),
                'Pending leave requests retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/leave/requests/{id}/pm-action',
        summary: 'Project Manager approves or rejects leave request',
        description: 'If approved, the request moves to General Manager. If rejected, the request is closed.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['action'],
                properties: [
                    new OA\Property(
                        property: 'action',
                        type: 'string',
                        enum: ['approved', 'rejected'],
                        example: 'approved',
                        description: 'Approval action'
                    ),
                    new OA\Property(
                        property: 'remarks',
                        type: 'string',
                        example: 'Approved — coverage arranged',
                        nullable: true,
                        description: 'Optional remarks from the approver'
                    ),
                ]
            )
        ),
        tags: ['Leave Requests'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID of the leave request',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Leave request actioned successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Leave request approved successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — PM and above only'),
            new OA\Response(response: 422, description: 'Leave request is not pending PM approval'),
        ]
    )]
    public function pmAction(ApproveLeaveRequest $request, int $id): JsonResponse
    {
        try {
            $leaveRequest = $this->leaveRequestService->pmAction(
                $id,
                $request->auth_user->id,
                $request->action,
                $request->remarks
            );
            return $this->successResponse(
                new LeaveRequestResource($leaveRequest),
                'Leave request ' . $request->action . ' successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/leave/requests/{id}/gm-action',
        summary: 'General Manager approves or rejects leave request',
        description: 'Final approval step. If approved, leave is confirmed. If rejected, request is closed.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['action'],
                properties: [
                    new OA\Property(
                        property: 'action',
                        type: 'string',
                        enum: ['approved', 'rejected'],
                        example: 'approved',
                        description: 'Final approval action'
                    ),
                    new OA\Property(
                        property: 'remarks',
                        type: 'string',
                        example: 'Approved by GM',
                        nullable: true,
                        description: 'Optional remarks from the GM'
                    ),
                ]
            )
        ),
        tags: ['Leave Requests'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID of the leave request',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Leave request actioned successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Leave request approved successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 422, description: 'Leave request is not pending GM approval'),
        ]
    )]
    public function gmAction(ApproveLeaveRequest $request, int $id): JsonResponse
    {
        try {
            $leaveRequest = $this->leaveRequestService->gmAction(
                $id,
                $request->auth_user->id,
                $request->action,
                $request->remarks
            );
            return $this->successResponse(
                new LeaveRequestResource($leaveRequest),
                'Leave request ' . $request->action . ' successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
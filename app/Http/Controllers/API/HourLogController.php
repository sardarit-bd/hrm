<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreHourLogRequest;
use App\Http\Requests\Project\UpdateHourLogRequest;
use App\Http\Resources\HourLogResource;
use App\Services\HourLogService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class HourLogController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected HourLogService $hourLogService
    ) {}

    #[OA\Get(
        path: '/api/v1/hour-logs',
        summary: 'List all hour logs with filters and pagination',
        security: [['bearerAuth' => []]],
        tags: ['Hour Logs'],
        parameters: [
            new OA\Parameter(
                name: 'project_id',
                in: 'query',
                required: false,
                description: 'Filter by project ID',
                schema: new OA\Schema(type: 'integer')
            ),
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
                    enum: ['pending', 'approved', 'rejected']
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
                description: 'Hour logs retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Hour logs retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'project', type: 'object'),
                                    new OA\Property(property: 'user', type: 'object'),
                                    new OA\Property(property: 'log_date', type: 'string', format: 'date'),
                                    new OA\Property(property: 'hours_logged', type: 'number', example: 6.5),
                                    new OA\Property(property: 'description', type: 'string', nullable: true),
                                    new OA\Property(
                                        property: 'status',
                                        type: 'string',
                                        enum: ['pending', 'approved', 'rejected']
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
            new OA\Response(response: 403, description: 'Forbidden — Team Leader and above only'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'project_id',
                'user_id',
                'status',
                'from_date',
                'to_date',
            ]);
            $perPage = $request->integer('per_page', 15);
            $logs    = $this->hourLogService->getPaginatedLogs($filters, $perPage);

            return $this->paginatedResponse(
                HourLogResource::collection($logs),
                'Hour logs retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/hour-logs',
        summary: 'Submit a new hour log entry',
        description: 'Employee logs hours worked on a project. Only applicable for hourly type projects.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['project_id', 'log_date', 'hours_logged'],
                properties: [
                    new OA\Property(
                        property: 'project_id',
                        type: 'integer',
                        example: 1,
                        description: 'ID of the hourly project'
                    ),
                    new OA\Property(
                        property: 'log_date',
                        type: 'string',
                        format: 'date',
                        example: '2026-03-21',
                        description: 'Date of work — cannot be in the future'
                    ),
                    new OA\Property(
                        property: 'hours_logged',
                        type: 'number',
                        example: 6.5,
                        description: 'Hours worked — minimum 0.5, maximum 24'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Worked on API integration and unit tests'
                    ),
                ]
            )
        ),
        tags: ['Hour Logs'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Hour log submitted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Hour log submitted successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'hours_logged', type: 'number', example: 6.5),
                                new OA\Property(property: 'status', type: 'string', example: 'pending'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreHourLogRequest $request): JsonResponse
    {
        try {
            $log = $this->hourLogService->submitLog(
                $request->validated(),
                $request->auth_user->id
            );
            return $this->createdResponse(
                new HourLogResource($log),
                'Hour log submitted successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/hour-logs/{id}',
        summary: 'Get hour log by ID',
        security: [['bearerAuth' => []]],
        tags: ['Hour Logs'],
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
                description: 'Hour log retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Hour log retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'project', type: 'object'),
                                new OA\Property(property: 'user', type: 'object'),
                                new OA\Property(property: 'approved_by', type: 'object', nullable: true),
                                new OA\Property(property: 'log_date', type: 'string', format: 'date'),
                                new OA\Property(property: 'hours_logged', type: 'number', example: 6.5),
                                new OA\Property(property: 'description', type: 'string', nullable: true),
                                new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                new OA\Property(property: 'approved_at', type: 'string', format: 'datetime', nullable: true),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Hour log not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $log = $this->hourLogService->findOrFail(
                $id,
                ['*'],
                ['project', 'user', 'approvedBy']
            );
            return $this->successResponse(
                new HourLogResource($log),
                'Hour log retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Hour log not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Put(
        path: '/api/v1/hour-logs/{id}',
        summary: 'Update a pending hour log',
        description: 'Only pending hour logs can be updated. Approved or rejected logs cannot be modified.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'log_date',
                        type: 'string',
                        format: 'date',
                        example: '2026-03-21'
                    ),
                    new OA\Property(
                        property: 'hours_logged',
                        type: 'number',
                        example: 7.0,
                        description: 'Updated hours — minimum 0.5, maximum 24'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Updated description'
                    ),
                ]
            )
        ),
        tags: ['Hour Logs'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Hour log updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Hour log not found'),
            new OA\Response(response: 422, description: 'Only pending hour logs can be updated'),
        ]
    )]
    public function update(UpdateHourLogRequest $request, int $id): JsonResponse
    {
        try {
            $log = $this->hourLogService->findOrFail($id);
            if (!$log->isPending()) {
                return $this->errorResponse('Only pending hour logs can be updated', 422);
            }
            $log = $this->hourLogService->update($id, $request->validated());
            return $this->successResponse(
                new HourLogResource($log),
                'Hour log updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Hour log not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/hour-logs/{id}',
        summary: 'Delete a pending hour log',
        description: 'Only pending hour logs can be deleted. Approved or rejected logs cannot be deleted.',
        security: [['bearerAuth' => []]],
        tags: ['Hour Logs'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Hour log deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Hour log not found'),
            new OA\Response(response: 422, description: 'Only pending hour logs can be deleted'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $log = $this->hourLogService->findOrFail($id);
            if (!$log->isPending()) {
                return $this->errorResponse('Only pending hour logs can be deleted', 422);
            }
            $this->hourLogService->delete($id);
            return $this->noContentResponse();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Hour log not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Patch(
        path: '/api/v1/hour-logs/{id}/approve',
        summary: 'Approve a pending hour log',
        description: 'PM or Team Leader approves the submitted hour log',
        security: [['bearerAuth' => []]],
        tags: ['Hour Logs'],
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
                description: 'Hour log approved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Hour log approved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'status', type: 'string', example: 'approved'),
                                new OA\Property(property: 'approved_by', type: 'object'),
                                new OA\Property(property: 'approved_at', type: 'string', format: 'datetime'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Team Leader and above only'),
            new OA\Response(response: 422, description: 'Only pending hour logs can be approved'),
        ]
    )]
    public function approve(int $id, Request $request): JsonResponse
    {
        try {
            $log = $this->hourLogService->approve($id, $request->auth_user->id);
            return $this->successResponse(
                new HourLogResource($log),
                'Hour log approved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Patch(
        path: '/api/v1/hour-logs/{id}/reject',
        summary: 'Reject a pending hour log',
        description: 'PM or Team Leader rejects the submitted hour log',
        security: [['bearerAuth' => []]],
        tags: ['Hour Logs'],
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
                description: 'Hour log rejected successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Hour log rejected successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'status', type: 'string', example: 'rejected'),
                                new OA\Property(property: 'approved_by', type: 'object'),
                                new OA\Property(property: 'approved_at', type: 'string', format: 'datetime'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Team Leader and above only'),
            new OA\Response(response: 422, description: 'Only pending hour logs can be rejected'),
        ]
    )]
    public function reject(int $id, Request $request): JsonResponse
    {
        try {
            $log = $this->hourLogService->reject($id, $request->auth_user->id);
            return $this->successResponse(
                new HourLogResource($log),
                'Hour log rejected successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/hour-logs/project/{projectId}/summary',
        summary: 'Get hour log summary for a project',
        description: 'Returns total approved hours, pending log count and total pending hours',
        security: [['bearerAuth' => []]],
        tags: ['Hour Logs'],
        parameters: [
            new OA\Parameter(
                name: 'projectId',
                in: 'path',
                required: true,
                description: 'ID of the project',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Project hour log summary retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Project hour log summary retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total_approved_hours', type: 'number', example: 120.5),
                                new OA\Property(property: 'pending_logs_count', type: 'integer', example: 3),
                                new OA\Property(property: 'total_pending_hours', type: 'number', example: 18.0),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Team Leader and above only'),
        ]
    )]
    public function projectSummary(int $projectId): JsonResponse
    {
        try {
            $totalHours = $this->hourLogService->getTotalHoursByProject($projectId);
            $pending    = $this->hourLogService->getPendingByProject($projectId);
            return $this->successResponse(
                [
                    'total_approved_hours' => $totalHours,
                    'pending_logs_count'   => count($pending['logs']),
                    'total_pending_hours'  => $pending['total_pending'],
                ],
                'Project hour log summary retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/hour-logs/my',
        summary: 'Get authenticated employee own hour logs',
        security: [['bearerAuth' => []]],
        tags: ['Hour Logs'],
        parameters: [
            new OA\Parameter(
                name: 'project_id',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['pending', 'approved', 'rejected']
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
            new OA\Response(response: 200, description: 'Hour logs retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function myLogs(Request $request): JsonResponse
    {
        try {
            $filters            = $request->only(['project_id', 'status', 'from_date', 'to_date']);
            $filters['user_id'] = $request->auth_user->id;
            $perPage            = $request->integer('per_page', 15);
            $logs               = $this->hourLogService->getPaginatedLogs($filters, $perPage);
            return $this->paginatedResponse($logs, 'Hour logs retrieved successfully');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\StoreLeaveTypeRequest;
use App\Http\Requests\Leave\UpdateLeaveTypeRequest;
use App\Http\Resources\LeaveTypeResource;
use App\Services\LeaveTypeService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class LeaveTypeController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected LeaveTypeService $leaveTypeService
    ) {}

    #[OA\Get(
        path: '/api/v1/leave/types',
        summary: 'List all leave types',
        security: [['bearerAuth' => []]],
        tags: ['Leave Types'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Leave types retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Leave types retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Sick Leave'),
                                    new OA\Property(property: 'max_days_per_year', type: 'integer', example: 14),
                                    new OA\Property(property: 'is_paid', type: 'boolean', example: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'datetime'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): JsonResponse
    {
        try {
            $types = $this->leaveTypeService->getAllLeaveTypes();
            return $this->successResponse(
                LeaveTypeResource::collection($types),
                'Leave types retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/leave/types',
        summary: 'Create a new leave type',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'max_days_per_year', 'is_paid'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Sick Leave',
                        description: 'Unique leave type name'
                    ),
                    new OA\Property(
                        property: 'max_days_per_year',
                        type: 'integer',
                        example: 14,
                        description: 'Maximum allowed days per year (1-365)'
                    ),
                    new OA\Property(
                        property: 'is_paid',
                        type: 'boolean',
                        example: true,
                        description: 'Whether this leave type is paid or unpaid'
                    ),
                ]
            )
        ),
        tags: ['Leave Types'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Leave type created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Leave type created successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin and GM only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreLeaveTypeRequest $request): JsonResponse
    {
        try {
            $type = $this->leaveTypeService->create($request->validated());
            return $this->createdResponse(
                new LeaveTypeResource($type),
                'Leave type created successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/leave/types/{id}',
        summary: 'Get leave type by ID',
        security: [['bearerAuth' => []]],
        tags: ['Leave Types'],
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
                description: 'Leave type retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Leave type retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Sick Leave'),
                                new OA\Property(property: 'max_days_per_year', type: 'integer', example: 14),
                                new OA\Property(property: 'is_paid', type: 'boolean', example: true),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Leave type not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $type = $this->leaveTypeService->findOrFail($id);
            return $this->successResponse(
                new LeaveTypeResource($type),
                'Leave type retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Leave type not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Put(
        path: '/api/v1/leave/types/{id}',
        summary: 'Update leave type',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Updated Leave Type'
                    ),
                    new OA\Property(
                        property: 'max_days_per_year',
                        type: 'integer',
                        example: 10
                    ),
                    new OA\Property(
                        property: 'is_paid',
                        type: 'boolean',
                        example: false
                    ),
                ]
            )
        ),
        tags: ['Leave Types'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Leave type updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin and GM only'),
            new OA\Response(response: 404, description: 'Leave type not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(
        UpdateLeaveTypeRequest $request,
        int $id
    ): JsonResponse {
        try {
            $type = $this->leaveTypeService->update($id, $request->validated());
            return $this->successResponse(
                new LeaveTypeResource($type),
                'Leave type updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Leave type not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/leave/types/{id}',
        summary: 'Delete leave type',
        security: [['bearerAuth' => []]],
        tags: ['Leave Types'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Leave type deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin and GM only'),
            new OA\Response(response: 404, description: 'Leave type not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->leaveTypeService->delete($id);
            return $this->noContentResponse();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Leave type not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
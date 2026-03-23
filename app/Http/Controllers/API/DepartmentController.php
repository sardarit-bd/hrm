<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DepartmentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected DepartmentService $departmentService
    ) {}

    #[OA\Get(
        path: '/api/v1/departments',
        summary: 'List all departments with pagination',
        security: [['bearerAuth' => []]],
        tags: ['Departments'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'is_active',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Departments retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Departments retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Engineering'),
                                    new OA\Property(property: 'description', type: 'string', nullable: true),
                                    new OA\Property(property: 'manager', type: 'object', nullable: true),
                                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
                                    new OA\Property(property: 'teams_count', type: 'integer', example: 3),
                                    new OA\Property(property: 'users_count', type: 'integer', example: 15),
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
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'is_active']);
            $perPage = $request->integer('per_page', 15);

            $departments = $this->departmentService->getPaginatedDepartments(
                $filters,
                $perPage
            );

            return $this->paginatedResponse(
                $departments,
                'Departments retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/departments',
        summary: 'Create a new department',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Engineering',
                        description: 'Unique department name'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Software engineering department'
                    ),
                    new OA\Property(
                        property: 'manager_id',
                        type: 'integer',
                        nullable: true,
                        example: 3,
                        description: 'ID of the department manager'
                    ),
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        example: true
                    ),
                ]
            )
        ),
        tags: ['Departments'],
        responses: [
            new OA\Response(response: 201, description: 'Department created successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        try {
            $department = $this->departmentService->create(
                $request->validated()
            );

            return $this->createdResponse(
                new DepartmentResource($department),
                'Department created successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/departments/{id}',
        summary: 'Get department by ID with teams and members',
        security: [['bearerAuth' => []]],
        tags: ['Departments'],
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
                description: 'Department retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Department retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Engineering'),
                                new OA\Property(property: 'manager', type: 'object', nullable: true),
                                new OA\Property(property: 'teams', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'teams_count', type: 'integer', example: 3),
                                new OA\Property(property: 'users_count', type: 'integer', example: 15),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Department not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $department = $this->departmentService->getDepartmentWithDetails($id);

            if (!$department) {
                return $this->notFoundResponse('Department not found');
            }

            return $this->successResponse(
                new DepartmentResource($department),
                'Department retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Put(
        path: '/api/v1/departments/{id}',
        summary: 'Update department details',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated Name'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'manager_id', type: 'integer', nullable: true),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
                ]
            )
        ),
        tags: ['Departments'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Department updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Department not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(
        UpdateDepartmentRequest $request,
        int $id
    ): JsonResponse {
        try {
            $department = $this->departmentService->update(
                $id,
                $request->validated()
            );

            return $this->successResponse(
                new DepartmentResource($department),
                'Department updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Department not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/departments/{id}',
        summary: 'Delete department permanently',
        description: 'Cannot delete a department that has active users or teams',
        security: [['bearerAuth' => []]],
        tags: ['Departments'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Department deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Department not found'),
            new OA\Response(response: 422, description: 'Cannot delete department with active users or teams'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $department = $this->departmentService->findOrFail($id);

            // Prevent deletion if department has users or teams
            if ($department->users()->count() > 0) {
                return $this->errorResponse(
                    'Cannot delete department that has employees assigned',
                    422
                );
            }

            if ($department->teams()->count() > 0) {
                return $this->errorResponse(
                    'Cannot delete department that has teams assigned',
                    422
                );
            }

            $this->departmentService->delete($id);

            return $this->noContentResponse();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Department not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/departments/active',
        summary: 'Get all active departments',
        security: [['bearerAuth' => []]],
        tags: ['Departments'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Active departments retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Active departments retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(type: 'object')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function getActive(): JsonResponse
    {
        try {
            $departments = $this->departmentService->getActiveDepartments();

            return $this->successResponse(
                DepartmentResource::collection($departments),
                'Active departments retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
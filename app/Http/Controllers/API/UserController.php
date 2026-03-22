<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected UserService $userService
    ) {}

    #[OA\Get(
        path: '/api/v1/users',
        summary: 'List all users with filters and pagination',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'role', in: 'query', required: false,
                schema: new OA\Schema(type: 'string', enum: ['super_admin', 'general_manager', 'project_manager', 'team_leader', 'employee'])
            ),
            new OA\Parameter(name: 'status', in: 'query', required: false,
                schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'terminated'])
            ),
            new OA\Parameter(name: 'department', in: 'query', required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(name: 'search', in: 'query', required: false,
                description: 'Search by name, email or employee code',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(name: 'per_page', in: 'query', required: false,
                schema: new OA\Schema(type: 'integer', example: 15)
            ),
            new OA\Parameter(name: 'page', in: 'query', required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Users retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Users retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'employee_code', type: 'string', example: 'EMP-0001'),
                                    new OA\Property(property: 'full_name', type: 'string', example: 'John Doe'),
                                    new OA\Property(property: 'email', type: 'string', example: 'john@company.com'),
                                    new OA\Property(property: 'role', type: 'string', example: 'employee'),
                                    new OA\Property(property: 'department', type: 'string', example: 'Engineering'),
                                    new OA\Property(property: 'designation', type: 'string', example: 'Senior Developer'),
                                    new OA\Property(property: 'status', type: 'string', example: 'active'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 100),
                                new OA\Property(property: 'last_page', type: 'integer', example: 7),
                            ]
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
            $filters = $request->only(['role', 'status', 'department', 'search']);
            $perPage = $request->integer('per_page', 15);
            $users   = $this->userService->getPaginatedUsers($filters, $perPage);
            return $this->paginatedResponse($users, 'Users retrieved successfully');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/users',
        summary: 'Create a new user',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['employee_code', 'full_name', 'email', 'password', 'role', 'joining_date'],
                properties: [
                    new OA\Property(property: 'employee_code', type: 'string', example: 'EMP-0007'),
                    new OA\Property(property: 'full_name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@company.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'Password@123'),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        enum: ['super_admin', 'general_manager', 'project_manager', 'team_leader', 'employee'],
                        example: 'employee'
                    ),
                    new OA\Property(property: 'department', type: 'string', example: 'Engineering'),
                    new OA\Property(property: 'designation', type: 'string', example: 'Senior Developer'),
                    new OA\Property(property: 'phone', type: 'string', example: '01700000007'),
                    new OA\Property(property: 'joining_date', type: 'string', format: 'date', example: '2026-01-01'),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'inactive', 'terminated'],
                        example: 'active'
                    ),
                ]
            )
        ),
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User created successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->create($request->validated());
            return $this->createdResponse(new UserResource($user), 'User created successfully');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/users/{id}',
        summary: 'Get user by ID with salary, roster and policy',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->findOrFail($id, ['*'], [
                'currentSalary',
                'currentRoster.shift',
                'currentPolicy.attendancePolicy',
            ]);
            return $this->successResponse(new UserResource($user), 'User retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('User not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Put(
        path: '/api/v1/users/{id}',
        summary: 'Update user details',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'employee_code', type: 'string', example: 'EMP-0007'),
                    new OA\Property(property: 'full_name', type: 'string', example: 'Updated Name'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'updated@company.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'NewPassword@123'),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        enum: ['super_admin', 'general_manager', 'project_manager', 'team_leader', 'employee']
                    ),
                    new OA\Property(property: 'department', type: 'string', example: 'Backend'),
                    new OA\Property(property: 'designation', type: 'string', example: 'Lead Developer'),
                    new OA\Property(property: 'phone', type: 'string', example: '01700000099'),
                    new OA\Property(property: 'joining_date', type: 'string', format: 'date', example: '2026-01-01'),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'inactive', 'terminated']
                    ),
                ]
            )
        ),
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin only'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = $this->userService->update($id, $request->validated());
            return $this->successResponse(new UserResource($user), 'User updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('User not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/users/{id}',
        summary: 'Delete user permanently',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin only'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->userService->delete($id);
            return $this->noContentResponse();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('User not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Patch(
        path: '/api/v1/users/{id}/status',
        summary: 'Change user account status',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'inactive', 'terminated'],
                        example: 'inactive'
                    ),
                ]
            )
        ),
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User status updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin only'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'status' => ['required', 'in:active,inactive,terminated'],
            ]);
            $user = $this->userService->changeStatus($id, $request->status);
            return $this->successResponse(new UserResource($user), 'User status updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('User not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/users/list/project-managers',
        summary: 'Get all project managers',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'Project managers retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function getProjectManagers(): JsonResponse
    {
        try {
            $managers = $this->userService->getProjectManagers();
            return $this->successResponse(
                UserResource::collection($managers),
                'Project managers retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/users/list/team-leaders',
        summary: 'Get all team leaders',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'Team leaders retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function getTeamLeaders(): JsonResponse
    {
        try {
            $leaders = $this->userService->getTeamLeaders();
            return $this->successResponse(
                UserResource::collection($leaders),
                'Team leaders retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/users/list/employees',
        summary: 'Get all employees',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'Employees retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function getEmployees(): JsonResponse
    {
        try {
            $employees = $this->userService->getEmployees();
            return $this->successResponse(
                UserResource::collection($employees),
                'Employees retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
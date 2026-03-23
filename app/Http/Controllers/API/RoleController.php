<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\AssignPermissionRequest;
use App\Http\Requests\Role\AssignRoleRequest;
use App\Http\Requests\Role\SyncPermissionsRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Services\RoleService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class RoleController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected RoleService $roleService
    ) {}

    #[OA\Get(
        path: '/api/v1/roles',
        summary: 'List all roles with their permissions',
        security: [['bearerAuth' => []]],
        tags: ['Roles & Permissions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Roles retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Roles retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'super_admin'),
                                    new OA\Property(
                                        property: 'permissions',
                                        type: 'array',
                                        items: new OA\Items(type: 'object')
                                    ),
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
            $roles = $this->roleService->getAllRoles();
            return $this->successResponse(
                RoleResource::collection($roles),
                'Roles retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/roles/{id}',
        summary: 'Get role by ID with all permissions',
        security: [['bearerAuth' => []]],
        tags: ['Roles & Permissions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Role retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Role not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $role = $this->roleService->getRoleById($id);
            if (!$role) {
                return $this->notFoundResponse('Role not found');
            }
            return $this->successResponse(
                new RoleResource($role),
                'Role retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/permissions',
        summary: 'List all permissions grouped by module',
        security: [['bearerAuth' => []]],
        tags: ['Roles & Permissions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Permissions retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Permissions retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            example: [
                                'users'    => ['users.view', 'users.create'],
                                'projects' => ['projects.view', 'projects.create'],
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function permissions(): JsonResponse
    {
        try {
            $permissions = $this->roleService->getAllPermissions();
            return $this->successResponse(
                $permissions,
                'Permissions retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/roles/{id}/permissions/sync',
        summary: 'Sync all permissions for a role',
        description: 'Replaces all existing permissions with the provided list',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['permissions'],
                properties: [
                    new OA\Property(
                        property: 'permissions',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['users.view', 'users.create', 'projects.view']
                    ),
                ]
            )
        ),
        tags: ['Roles & Permissions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Permissions synced successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin only'),
            new OA\Response(response: 404, description: 'Role not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function syncPermissions(
        SyncPermissionsRequest $request,
        int $id
    ): JsonResponse {
        try {
            $role = $this->roleService->assignPermissionsToRole(
                $id,
                $request->permissions
            );
            return $this->successResponse(
                new RoleResource($role),
                'Permissions synced successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/roles/{id}/permissions/give',
        summary: 'Give a single permission to a role',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['permission'],
                properties: [
                    new OA\Property(
                        property: 'permission',
                        type: 'string',
                        example: 'users.create'
                    ),
                ]
            )
        ),
        tags: ['Roles & Permissions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Permission given successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin only'),
            new OA\Response(response: 404, description: 'Role not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function givePermission(
        AssignPermissionRequest $request,
        int $id
    ): JsonResponse {
        try {
            $role = $this->roleService->givePermissionToRole(
                $id,
                $request->permission
            );
            return $this->successResponse(
                new RoleResource($role),
                'Permission given successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/roles/{id}/permissions/revoke',
        summary: 'Revoke a single permission from a role',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['permission'],
                properties: [
                    new OA\Property(
                        property: 'permission',
                        type: 'string',
                        example: 'users.create'
                    ),
                ]
            )
        ),
        tags: ['Roles & Permissions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Permission revoked successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin only'),
            new OA\Response(response: 404, description: 'Role not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function revokePermission(
        AssignPermissionRequest $request,
        int $id
    ): JsonResponse {
        try {
            $role = $this->roleService->revokePermissionFromRole(
                $id,
                $request->permission
            );
            return $this->successResponse(
                new RoleResource($role),
                'Permission revoked successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/roles/assign',
        summary: 'Assign a role to a user',
        description: 'Replaces the user existing role with the new one',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id', 'role'],
                properties: [
                    new OA\Property(
                        property: 'user_id',
                        type: 'integer',
                        example: 5,
                        description: 'ID of the user'
                    ),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        example: 'project_manager',
                        description: 'Role name to assign'
                    ),
                ]
            )
        ),
        tags: ['Roles & Permissions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Role assigned successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Role assigned successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin only'),
            new OA\Response(response: 404, description: 'User or role not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function assignRole(AssignRoleRequest $request): JsonResponse
    {
        try {
            $user = $this->roleService->assignRoleToUser(
                $request->user_id,
                $request->role
            );
            return $this->successResponse(
                new UserResource($user),
                'Role assigned successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/roles/user/{userId}/permissions',
        summary: 'Get all permissions for a specific user',
        security: [['bearerAuth' => []]],
        tags: ['Roles & Permissions'],
        parameters: [
            new OA\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                description: 'ID of the user',
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User permissions retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User permissions retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'roles',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['project_manager']
                                ),
                                new OA\Property(
                                    property: 'permissions',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['projects.view', 'projects.create']
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    public function userPermissions(int $userId): JsonResponse
    {
        try {
            $data = $this->roleService->getUserPermissions($userId);
            return $this->successResponse(
                $data,
                'User permissions retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('User not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
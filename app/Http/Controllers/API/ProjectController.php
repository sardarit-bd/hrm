<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProjectController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ProjectService $projectService
    ) {}

    #[OA\Get(
        path: '/api/v1/projects',
        summary: 'List all projects with filters and pagination',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['ongoing', 'delivered', 'cancelled']
                )
            ),
            new OA\Parameter(
                name: 'type',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['single', 'milestone', 'hourly']
                )
            ),
            new OA\Parameter(
                name: 'project_manager_id',
                in: 'query',
                required: false,
                description: 'Filter by project manager ID',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search by project name or client name',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'from_date',
                in: 'query',
                required: false,
                description: 'Filter projects starting from this date',
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'to_date',
                in: 'query',
                required: false,
                description: 'Filter projects with deadline up to this date',
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
                description: 'Projects retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Projects retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'E-Commerce Platform'),
                                    new OA\Property(property: 'client_name', type: 'string', example: 'ABC Corp'),
                                    new OA\Property(property: 'type', type: 'string', example: 'milestone'),
                                    new OA\Property(property: 'status', type: 'string', example: 'ongoing'),
                                    new OA\Property(property: 'total_budget', type: 'number', example: 50000),
                                    new OA\Property(property: 'currency', type: 'string', example: 'USD'),
                                    new OA\Property(property: 'start_date', type: 'string', format: 'date'),
                                    new OA\Property(property: 'deadline', type: 'string', format: 'date'),
                                    new OA\Property(property: 'is_overdue', type: 'boolean', example: false),
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
            $filters  = $request->only(['status', 'type', 'project_manager_id', 'search', 'from_date', 'to_date']);
            $perPage  = $request->integer('per_page', 15);
            $projects = $this->projectService->getPaginatedProjects($filters, $perPage);
            return $this->paginatedResponse($projects, 'Projects retrieved successfully');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/projects',
        summary: 'Create a new project',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'name',
                    'client_name',
                    'project_manager_id',
                    'type',
                    'total_budget',
                    'currency',
                    'exchange_rate_snapshot',
                    'start_date',
                    'deadline',
                ],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'E-Commerce Platform'
                    ),
                    new OA\Property(
                        property: 'client_name',
                        type: 'string',
                        example: 'ABC Corp'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Full e-commerce solution with payment integration'
                    ),
                    new OA\Property(
                        property: 'project_manager_id',
                        type: 'integer',
                        example: 3,
                        description: 'ID of the project manager'
                    ),
                    new OA\Property(
                        property: 'type',
                        type: 'string',
                        enum: ['single', 'milestone', 'hourly'],
                        example: 'milestone',
                        description: 'single = fixed price, milestone = paid per milestone, hourly = billed by hours'
                    ),
                    new OA\Property(
                        property: 'total_budget',
                        type: 'number',
                        example: 50000,
                        description: 'Total project budget in foreign currency'
                    ),
                    new OA\Property(
                        property: 'currency',
                        type: 'string',
                        example: 'USD',
                        description: 'Currency code e.g. USD, GBP, EUR'
                    ),
                    new OA\Property(
                        property: 'exchange_rate_snapshot',
                        type: 'number',
                        example: 110.50,
                        description: 'Exchange rate at time of project creation'
                    ),
                    new OA\Property(
                        property: 'start_date',
                        type: 'string',
                        format: 'date',
                        example: '2026-04-01'
                    ),
                    new OA\Property(
                        property: 'deadline',
                        type: 'string',
                        format: 'date',
                        example: '2026-09-30',
                        description: 'Must be after start_date'
                    ),
                ]
            )
        ),
        tags: ['Projects'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Project created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Project created successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreProjectRequest $request): JsonResponse
    {
        try {
            $project = $this->projectService->create($request->validated());
            return $this->createdResponse(
                new ProjectResource($project),
                'Project created successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/projects/{id}',
        summary: 'Get project by ID with teams and milestones',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
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
                description: 'Project retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Project retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'E-Commerce Platform'),
                                new OA\Property(property: 'client_name', type: 'string', example: 'ABC Corp'),
                                new OA\Property(property: 'type', type: 'string', example: 'milestone'),
                                new OA\Property(property: 'status', type: 'string', example: 'ongoing'),
                                new OA\Property(property: 'project_manager', type: 'object'),
                                new OA\Property(property: 'teams', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'milestones', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'is_overdue', type: 'boolean', example: false),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Project not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $project = $this->projectService->findOrFail(
                $id,
                ['*'],
                ['projectManager', 'teams', 'milestones']
            );
            return $this->successResponse(
                new ProjectResource($project),
                'Project retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Project not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Put(
        path: '/api/v1/projects/{id}',
        summary: 'Update project details',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated Project Name'),
                    new OA\Property(property: 'client_name', type: 'string', example: 'Updated Client'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'project_manager_id', type: 'integer', example: 3),
                    new OA\Property(property: 'type', type: 'string', enum: ['single', 'milestone', 'hourly']),
                    new OA\Property(property: 'total_budget', type: 'number', example: 60000),
                    new OA\Property(property: 'currency', type: 'string', example: 'USD'),
                    new OA\Property(property: 'exchange_rate_snapshot', type: 'number', example: 112.00),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'deadline', type: 'string', format: 'date', example: '2026-10-31'),
                    new OA\Property(property: 'delivered_date', type: 'string', format: 'date', nullable: true),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['ongoing', 'delivered', 'cancelled']
                    ),
                ]
            )
        ),
        tags: ['Projects'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Project updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — PM and above only'),
            new OA\Response(response: 404, description: 'Project not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateProjectRequest $request, int $id): JsonResponse
    {
        try {
            $project = $this->projectService->update($id, $request->validated());
            return $this->successResponse(
                new ProjectResource($project),
                'Project updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Project not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/projects/{id}',
        summary: 'Delete project permanently',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Project deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 404, description: 'Project not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->projectService->delete($id);
            return $this->noContentResponse();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Project not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Patch(
        path: '/api/v1/projects/{id}/status',
        summary: 'Update project status',
        description: 'When status is set to delivered, delivered_date is automatically set to today',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['ongoing', 'delivered', 'cancelled'],
                        example: 'delivered'
                    ),
                ]
            )
        ),
        tags: ['Projects'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Project status updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — PM and above only'),
            new OA\Response(response: 404, description: 'Project not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'status' => ['required', 'in:ongoing,delivered,cancelled'],
            ]);
            $project = $this->projectService->updateStatus($id, $request->status);
            return $this->successResponse(
                new ProjectResource($project),
                'Project status updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Project not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/projects/ongoing',
        summary: 'Get all ongoing projects ordered by deadline',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ongoing projects retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Ongoing projects retrieved successfully'),
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
    public function getOngoing(): JsonResponse
    {
        try {
            $projects = $this->projectService->getOngoingProjects();
            return $this->successResponse(
                ProjectResource::collection($projects),
                'Ongoing projects retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/projects/overdue',
        summary: 'Get all overdue projects — ongoing projects past their deadline',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Overdue projects retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Overdue projects retrieved successfully'),
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
    public function getOverdue(): JsonResponse
    {
        try {
            $projects = $this->projectService->getOverdueProjects();
            return $this->successResponse(
                ProjectResource::collection($projects),
                'Overdue projects retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/projects/my',
        summary: 'Get projects for the authenticated user',
        description: 'PM sees projects they manage. Other roles see projects they are assigned to.',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Projects retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Projects retrieved successfully'),
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
    public function myProjects(Request $request): JsonResponse
    {
        try {
            $user = $request->auth_user;
            if ($user->isProjectManager()) {
                $projects = $this->projectService->getProjectsByManager($user->id);
            } else {
                $projects = $this->projectService->getProjectsForUser($user->id);
            }
            return $this->successResponse(
                ProjectResource::collection($projects),
                'Projects retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
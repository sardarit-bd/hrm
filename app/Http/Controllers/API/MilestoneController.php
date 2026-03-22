<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreMilestoneRequest;
use App\Http\Requests\Project\UpdateMilestoneRequest;
use App\Http\Resources\MilestoneResource;
use App\Services\MilestoneService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MilestoneController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected MilestoneService $milestoneService
    ) {}

    #[OA\Get(
        path: '/api/v1/milestones',
        summary: 'List all milestones for a project',
        security: [['bearerAuth' => []]],
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'project_id',
                in: 'query',
                required: true,
                description: 'ID of the project to get milestones for',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Milestones retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Milestones retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'title', type: 'string', example: 'Phase 1 Delivery'),
                                    new OA\Property(property: 'description', type: 'string', nullable: true),
                                    new OA\Property(property: 'due_date', type: 'string', format: 'date'),
                                    new OA\Property(property: 'completion_date', type: 'string', format: 'date', nullable: true),
                                    new OA\Property(property: 'milestone_value', type: 'number', example: 15000),
                                    new OA\Property(property: 'currency', type: 'string', example: 'USD'),
                                    new OA\Property(
                                        property: 'status',
                                        type: 'string',
                                        enum: ['pending', 'completed', 'missed']
                                    ),
                                    new OA\Property(property: 'is_overdue', type: 'boolean', example: false),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'project_id is required'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'project_id' => ['required', 'integer', 'exists:projects,id'],
            ]);
            $milestones = $this->milestoneService->getMilestonesByProject(
                $request->project_id
            );
            return $this->successResponse(
                MilestoneResource::collection($milestones),
                'Milestones retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/milestones',
        summary: 'Create a new milestone for a project',
        description: 'Only applicable for milestone type projects',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'project_id',
                    'title',
                    'due_date',
                    'milestone_value',
                    'currency',
                ],
                properties: [
                    new OA\Property(
                        property: 'project_id',
                        type: 'integer',
                        example: 1,
                        description: 'ID of the project'
                    ),
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        example: 'Phase 1 Delivery',
                        description: 'Milestone title'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Complete frontend development'
                    ),
                    new OA\Property(
                        property: 'due_date',
                        type: 'string',
                        format: 'date',
                        example: '2026-05-01',
                        description: 'Target completion date'
                    ),
                    new OA\Property(
                        property: 'milestone_value',
                        type: 'number',
                        example: 15000,
                        description: 'Earnings for this milestone'
                    ),
                    new OA\Property(
                        property: 'currency',
                        type: 'string',
                        example: 'USD',
                        description: 'Currency code'
                    ),
                ]
            )
        ),
        tags: ['Milestones'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Milestone created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Milestone created successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — PM and above only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreMilestoneRequest $request): JsonResponse
    {
        try {
            $milestone = $this->milestoneService->create($request->validated());
            return $this->createdResponse(
                new MilestoneResource($milestone),
                'Milestone created successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/milestones/{id}',
        summary: 'Get milestone by ID',
        security: [['bearerAuth' => []]],
        tags: ['Milestones'],
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
                description: 'Milestone retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Milestone retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Milestone not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $milestone = $this->milestoneService->findOrFail(
                $id,
                ['*'],
                ['project']
            );
            return $this->successResponse(
                new MilestoneResource($milestone),
                'Milestone retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Milestone not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Put(
        path: '/api/v1/milestones/{id}',
        summary: 'Update milestone details',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Updated Milestone Title'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2026-05-15'),
                    new OA\Property(property: 'completion_date', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'milestone_value', type: 'number', example: 18000),
                    new OA\Property(property: 'currency', type: 'string', example: 'USD'),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['pending', 'completed', 'missed']
                    ),
                ]
            )
        ),
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Milestone updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — PM and above only'),
            new OA\Response(response: 404, description: 'Milestone not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateMilestoneRequest $request, int $id): JsonResponse
    {
        try {
            $milestone = $this->milestoneService->update($id, $request->validated());
            return $this->successResponse(
                new MilestoneResource($milestone),
                'Milestone updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Milestone not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/milestones/{id}',
        summary: 'Delete milestone permanently',
        security: [['bearerAuth' => []]],
        tags: ['Milestones'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Milestone deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — PM and above only'),
            new OA\Response(response: 404, description: 'Milestone not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->milestoneService->delete($id);
            return $this->noContentResponse();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Milestone not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Patch(
        path: '/api/v1/milestones/{id}/complete',
        summary: 'Mark milestone as completed',
        description: 'Sets status to completed and records the completion date as today',
        security: [['bearerAuth' => []]],
        tags: ['Milestones'],
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
                description: 'Milestone marked as completed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Milestone marked as completed'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'status', type: 'string', example: 'completed'),
                                new OA\Property(property: 'completion_date', type: 'string', format: 'date'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — PM and above only'),
            new OA\Response(response: 404, description: 'Milestone not found'),
        ]
    )]
    public function markAsCompleted(int $id): JsonResponse
    {
        try {
            $milestone = $this->milestoneService->markAsCompleted($id);
            return $this->successResponse(
                new MilestoneResource($milestone),
                'Milestone marked as completed'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Milestone not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Patch(
        path: '/api/v1/milestones/{id}/missed',
        summary: 'Mark milestone as missed',
        description: 'Sets status to missed — used when a deadline is passed without completion',
        security: [['bearerAuth' => []]],
        tags: ['Milestones'],
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
                description: 'Milestone marked as missed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Milestone marked as missed'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'status', type: 'string', example: 'missed'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — PM and above only'),
            new OA\Response(response: 404, description: 'Milestone not found'),
        ]
    )]
    public function markAsMissed(int $id): JsonResponse
    {
        try {
            $milestone = $this->milestoneService->markAsMissed($id);
            return $this->successResponse(
                new MilestoneResource($milestone),
                'Milestone marked as missed'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Milestone not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/milestones/overdue',
        summary: 'Get all overdue milestones across all projects',
        description: 'Returns pending milestones whose due_date has passed',
        security: [['bearerAuth' => []]],
        tags: ['Milestones'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Overdue milestones retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Overdue milestones retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'title', type: 'string', example: 'Phase 1 Delivery'),
                                    new OA\Property(property: 'due_date', type: 'string', format: 'date'),
                                    new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                    new OA\Property(property: 'is_overdue', type: 'boolean', example: true),
                                    new OA\Property(property: 'project', type: 'object'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — PM and above only'),
        ]
    )]
    public function getOverdue(): JsonResponse
    {
        try {
            $milestones = $this->milestoneService->getOverdueMilestones();
            return $this->successResponse(
                MilestoneResource::collection($milestones),
                'Overdue milestones retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
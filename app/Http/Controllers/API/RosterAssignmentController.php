<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Roster\StoreRosterRequest;
use App\Http\Resources\RosterAssignmentResource;
use App\Services\RosterAssignmentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class RosterAssignmentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected RosterAssignmentService $rosterService
    ) {}

    #[OA\Get(
        path: '/api/v1/roster',
        summary: 'List all roster assignments with pagination',
        security: [['bearerAuth' => []]],
        tags: ['Roster'],
        parameters: [
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
                description: 'Roster assignments retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Roster assignments retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'user', type: 'object'),
                                    new OA\Property(property: 'shift', type: 'object'),
                                    new OA\Property(
                                        property: 'weekend_days',
                                        type: 'array',
                                        items: new OA\Items(type: 'string'),
                                        example: ['friday', 'saturday']
                                    ),
                                    new OA\Property(property: 'effective_from', type: 'string', format: 'date'),
                                    new OA\Property(property: 'effective_to', type: 'string', format: 'date', nullable: true),
                                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
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
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->integer('per_page', 15);
            $rosters = $this->rosterService->getPaginated(
                $perPage,
                ['*'],
                ['user', 'shift', 'assignedBy']
            );
            return $this->paginatedResponse(
                $rosters,
                'Roster assignments retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/roster',
        summary: 'Assign roster to an employee',
        description: 'Assigns a shift and weekend days to an employee. Automatically closes the previous active roster.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id', 'shift_id', 'weekend_days', 'effective_from'],
                properties: [
                    new OA\Property(
                        property: 'user_id',
                        type: 'integer',
                        example: 5,
                        description: 'ID of the employee'
                    ),
                    new OA\Property(
                        property: 'shift_id',
                        type: 'integer',
                        example: 1,
                        description: 'ID of the shift to assign'
                    ),
                    new OA\Property(
                        property: 'weekend_days',
                        type: 'array',
                        items: new OA\Items(
                            type: 'string',
                            enum: ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday']
                        ),
                        example: ['friday', 'saturday'],
                        description: 'Days off for this employee'
                    ),
                    new OA\Property(
                        property: 'effective_from',
                        type: 'string',
                        format: 'date',
                        example: '2026-03-01',
                        description: 'Date from which this roster is effective'
                    ),
                ]
            )
        ),
        tags: ['Roster'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Roster assigned successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Roster assigned successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreRosterRequest $request): JsonResponse
    {
        try {
            $roster = $this->rosterService->assignRoster(
                $request->validated(),
                $request->auth_user->id
            );
            return $this->createdResponse(
                new RosterAssignmentResource($roster),
                'Roster assigned successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/roster/{id}',
        summary: 'Get roster assignment by ID',
        security: [['bearerAuth' => []]],
        tags: ['Roster'],
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
                description: 'Roster assignment retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Roster assignment retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Roster assignment not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $roster = $this->rosterService->findOrFail(
                $id,
                ['*'],
                ['user', 'shift', 'assignedBy']
            );
            return $this->successResponse(
                new RosterAssignmentResource($roster),
                'Roster assignment retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Roster assignment not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/roster/user/{userId}',
        summary: 'Get current active roster for a specific employee',
        security: [['bearerAuth' => []]],
        tags: ['Roster'],
        parameters: [
            new OA\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                description: 'ID of the employee',
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Active roster retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Active roster retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'shift', type: 'object'),
                                new OA\Property(
                                    property: 'weekend_days',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['friday', 'saturday']
                                ),
                                new OA\Property(property: 'effective_from', type: 'string', format: 'date'),
                                new OA\Property(property: 'effective_to', type: 'string', format: 'date', nullable: true),
                                new OA\Property(property: 'is_active', type: 'boolean', example: true),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'No active roster found for this user'),
        ]
    )]
    public function getUserRoster(int $userId): JsonResponse
    {
        try {
            $roster = $this->rosterService->getActiveRoster($userId);
            if (!$roster) {
                return $this->notFoundResponse('No active roster found for this user');
            }
            return $this->successResponse(
                new RosterAssignmentResource($roster),
                'Active roster retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/roster/user/{userId}/history',
        summary: 'Get full roster history for a specific employee',
        description: 'Returns all past and current roster assignments for an employee in descending order',
        security: [['bearerAuth' => []]],
        tags: ['Roster'],
        parameters: [
            new OA\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                description: 'ID of the employee',
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Roster history retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Roster history retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(type: 'object')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function getRosterHistory(int $userId): JsonResponse
    {
        try {
            $history = $this->rosterService->getRosterHistory($userId);
            return $this->successResponse(
                RosterAssignmentResource::collection($history),
                'Roster history retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/roster/shift/{shiftId}/users',
        summary: 'Get all employees currently assigned to a specific shift',
        security: [['bearerAuth' => []]],
        tags: ['Roster'],
        parameters: [
            new OA\Parameter(
                name: 'shiftId',
                in: 'path',
                required: true,
                description: 'ID of the shift',
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
                            items: new OA\Items(type: 'object')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function getUsersByShift(int $shiftId): JsonResponse
    {
        try {
            $rosters = $this->rosterService->getUsersByShift($shiftId);
            return $this->successResponse(
                RosterAssignmentResource::collection($rosters),
                'Users retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
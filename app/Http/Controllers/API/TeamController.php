<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\AssignProjectMemberRequest;
use App\Http\Requests\Team\AssignTeamMemberRequest;
use App\Http\Requests\Team\AssignTeamProjectRequest;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Services\TeamService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TeamController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected TeamService $teamService
    ) {}

    #[OA\Get(
        path: '/api/v1/teams',
        summary: 'List all teams with pagination and filters',
        security: [['bearerAuth' => []]],
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search by team name',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'has_leader',
                in: 'query',
                required: false,
                description: 'Filter teams with or without a leader',
                schema: new OA\Schema(type: 'boolean')
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
                description: 'Teams retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Teams retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Backend Team A'),
                                    new OA\Property(property: 'leader', type: 'object', nullable: true),
                                    new OA\Property(property: 'has_leader', type: 'boolean', example: true),
                                    new OA\Property(property: 'members_count', type: 'integer', example: 5),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 10),
                                new OA\Property(property: 'last_page', type: 'integer', example: 1),
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
            $filters = $request->only(['search', 'has_leader']);
            $perPage = $request->integer('per_page', 15);
            $teams   = $this->teamService->getPaginatedTeams($filters, $perPage);

            return $this->paginatedResponse(
                TeamResource::collection($teams),
                'Teams retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/teams',
        summary: 'Create a new team',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Backend Team A',
                        description: 'Unique team name'
                    ),
                    new OA\Property(
                        property: 'leader_id',
                        type: 'integer',
                        example: 4,
                        nullable: true,
                        description: 'ID of the team leader — optional'
                    ),
                ]
            )
        ),
        tags: ['Teams'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Team created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Team created successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreTeamRequest $request): JsonResponse
    {
        try {
            $data               = $request->validated();
            $data['created_by'] = $request->auth_user->id;
            $team               = $this->teamService->create($data);
            return $this->createdResponse(
                new TeamResource($team),
                'Team created successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/teams/{id}',
        summary: 'Get team by ID with members and projects',
        security: [['bearerAuth' => []]],
        tags: ['Teams'],
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
                description: 'Team retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Team retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Backend Team A'),
                                new OA\Property(property: 'leader', type: 'object', nullable: true),
                                new OA\Property(property: 'members', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'projects', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'has_leader', type: 'boolean', example: true),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Team not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $team = $this->teamService->findOrFail(
                $id,
                ['*'],
                ['leader', 'createdBy', 'members', 'projects']
            );
            return $this->successResponse(
                new TeamResource($team),
                'Team retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Team not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Put(
        path: '/api/v1/teams/{id}',
        summary: 'Update team details',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated Team Name'),
                    new OA\Property(
                        property: 'leader_id',
                        type: 'integer',
                        example: 4,
                        nullable: true,
                        description: 'Set to null to remove leader'
                    ),
                ]
            )
        ),
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Team updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 404, description: 'Team not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateTeamRequest $request, int $id): JsonResponse
    {
        try {
            $team = $this->teamService->update($id, $request->validated());
            return $this->successResponse(
                new TeamResource($team),
                'Team updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Team not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/teams/{id}',
        summary: 'Delete team permanently',
        security: [['bearerAuth' => []]],
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Team deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 404, description: 'Team not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->teamService->delete($id);
            return $this->noContentResponse();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Team not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/teams/{team}/members',
        summary: 'Add a member to a team',
        description: 'Project Manager adds an employee to a team. Employee must not already be an active member.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id', 'joined_at'],
                properties: [
                    new OA\Property(
                        property: 'user_id',
                        type: 'integer',
                        example: 5,
                        description: 'ID of the employee to add'
                    ),
                    new OA\Property(
                        property: 'joined_at',
                        type: 'string',
                        format: 'date',
                        example: '2026-03-01',
                        description: 'Date the employee joined the team'
                    ),
                ]
            )
        ),
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'team',
                in: 'path',
                required: true,
                description: 'ID of the team',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Member added to team successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Member added to team successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — PM and above only'),
            new OA\Response(response: 422, description: 'User is already an active member of this team'),
        ]
    )]
    public function addMember(AssignTeamMemberRequest $request, int $teamId): JsonResponse
    {
        try {
            $member = $this->teamService->addMember(
                $teamId,
                $request->user_id,
                $request->joined_at
            );
            return $this->createdResponse($member, 'Member added to team successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/teams/{team}/members/{user}',
        summary: 'Remove a member from a team',
        description: 'Sets the left_at date to today. Member history is preserved.',
        security: [['bearerAuth' => []]],
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'team',
                in: 'path',
                required: true,
                description: 'ID of the team',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'ID of the employee to remove',
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Member removed from team successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — PM and above only'),
            new OA\Response(response: 422, description: 'User is not an active member of this team'),
        ]
    )]
    public function removeMember(int $teamId, int $userId): JsonResponse
    {
        try {
            $this->teamService->removeMember($teamId, $userId);
            return $this->noContentResponse();
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/teams/{team}/projects',
        summary: 'Assign a team to a project',
        description: 'Project Manager assigns an existing team to a project. A team cannot be assigned twice to the same project.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['project_id', 'assigned_at'],
                properties: [
                    new OA\Property(
                        property: 'project_id',
                        type: 'integer',
                        example: 1,
                        description: 'ID of the project'
                    ),
                    new OA\Property(
                        property: 'assigned_at',
                        type: 'string',
                        format: 'date',
                        example: '2026-03-01',
                        description: 'Date of assignment'
                    ),
                ]
            )
        ),
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'team',
                in: 'path',
                required: true,
                description: 'ID of the team',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Team assigned to project successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Team assigned to project successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — PM and above only'),
            new OA\Response(response: 422, description: 'Team is already assigned to this project'),
        ]
    )]
    public function assignToProject(AssignTeamProjectRequest $request, int $teamId): JsonResponse
    {
        try {
            $assignment = $this->teamService->assignToProject(
                $teamId,
                $request->project_id,
                $request->auth_user->id,
                $request->assigned_at
            );
            return $this->createdResponse($assignment, 'Team assigned to project successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/teams/project-assignments/{assignmentId}/members',
        summary: 'Assign a team member to a specific project',
        description: 'Team Leader assigns a member from the team to work on a specific project.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id', 'team_project_assignment_id', 'assigned_at'],
                properties: [
                    new OA\Property(
                        property: 'user_id',
                        type: 'integer',
                        example: 5,
                        description: 'ID of the team member to assign'
                    ),
                    new OA\Property(
                        property: 'team_project_assignment_id',
                        type: 'integer',
                        example: 1,
                        description: 'ID of the team-project assignment'
                    ),
                    new OA\Property(
                        property: 'assigned_at',
                        type: 'string',
                        format: 'date',
                        example: '2026-03-01'
                    ),
                ]
            )
        ),
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'assignmentId',
                in: 'path',
                required: true,
                description: 'ID of the team-project assignment',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Member assigned to project successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Member assigned to project successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Team Leader and above only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function assignMemberToProject(AssignProjectMemberRequest $request, int $assignmentId): JsonResponse
    {
        try {
            $data                               = $request->validated();
            $data['team_project_assignment_id'] = $assignmentId;
            $assignment                         = $this->teamService->assignMemberToProject(
                $data,
                $request->auth_user->id
            );
            return $this->createdResponse($assignment, 'Member assigned to project successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/teams/project-assignments/{assignmentId}/members/{userId}',
        summary: 'Release a member from a project',
        description: 'Sets the released_at date to today. Assignment history is preserved.',
        security: [['bearerAuth' => []]],
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'assignmentId',
                in: 'path',
                required: true,
                description: 'ID of the team-project assignment',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                description: 'ID of the employee to release',
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Member released from project successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Team Leader and above only'),
        ]
    )]
    public function releaseMemberFromProject(int $assignmentId, int $userId): JsonResponse
    {
        try {
            $this->teamService->releaseMemberFromProject($assignmentId, $userId);
            return $this->noContentResponse();
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/teams/project-assignments/{assignmentId}/members',
        summary: 'Get all active members assigned to a project',
        security: [['bearerAuth' => []]],
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'assignmentId',
                in: 'path',
                required: true,
                description: 'ID of the team-project assignment',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Project members retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Project members retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(type: 'object')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Team Leader and above only'),
        ]
    )]
    public function getProjectMembers(int $assignmentId): JsonResponse
    {
        try {
            $members = $this->teamService->getProjectMembers($assignmentId);
            return $this->successResponse($members, 'Project members retrieved successfully');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/teams/my',
        summary: 'Get teams led by the authenticated Team Leader',
        security: [['bearerAuth' => []]],
        tags: ['Teams'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Teams retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Teams retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(type: 'object')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Team Leader and above only'),
        ]
    )]
    public function myTeams(Request $request): JsonResponse
    {
        try {
            $teams = $this->teamService->getTeamsByLeader($request->auth_user->id);
            return $this->successResponse(
                TeamResource::collection($teams),
                'Teams retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
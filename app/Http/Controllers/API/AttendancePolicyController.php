<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AssignPolicyRequest;
use App\Http\Requests\Attendance\StorePolicyRequest;
use App\Http\Requests\Attendance\UpdatePolicyRequest;
use App\Http\Resources\AttendancePolicyResource;
use App\Services\AttendancePolicyService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AttendancePolicyController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AttendancePolicyService $policyService
    ) {}

    #[OA\Get(
        path: '/api/v1/attendance/policies',
        summary: 'List all active attendance policies',
        security: [['bearerAuth' => []]],
        tags: ['Attendance Policies'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Attendance policies retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Attendance policies retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Standard Policy'),
                                    new OA\Property(property: 'grace_period_minutes', type: 'integer', example: 10),
                                    new OA\Property(property: 'late_count_threshold', type: 'integer', example: 15),
                                    new OA\Property(property: 'late_threshold_deduction_days', type: 'number', example: 1.0),
                                    new OA\Property(property: 'absent_deduction_per_day', type: 'number', example: 1.0),
                                    new OA\Property(property: 'half_day_threshold_hours', type: 'number', example: 4.0),
                                    new OA\Property(property: 'effective_from', type: 'string', format: 'date'),
                                    new OA\Property(property: 'effective_to', type: 'string', format: 'date', nullable: true),
                                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
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
            $policies = $this->policyService->getActivePolicies();
            return $this->successResponse(
                AttendancePolicyResource::collection($policies),
                'Attendance policies retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/attendance/policies',
        summary: 'Create a new attendance policy',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'name',
                    'grace_period_minutes',
                    'late_count_threshold',
                    'late_threshold_deduction_days',
                    'absent_deduction_per_day',
                    'half_day_threshold_hours',
                    'effective_from',
                ],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Standard Policy',
                        description: 'Unique policy name'
                    ),
                    new OA\Property(
                        property: 'grace_period_minutes',
                        type: 'integer',
                        example: 10,
                        description: 'Minutes allowed after shift start before marking late (0-60)'
                    ),
                    new OA\Property(
                        property: 'late_count_threshold',
                        type: 'integer',
                        example: 15,
                        description: 'Number of late marks that trigger a deduction'
                    ),
                    new OA\Property(
                        property: 'late_threshold_deduction_days',
                        type: 'number',
                        example: 1.0,
                        description: 'Days deducted per threshold breach'
                    ),
                    new OA\Property(
                        property: 'absent_deduction_per_day',
                        type: 'number',
                        example: 1.0,
                        description: 'Multiplier for absent day deduction'
                    ),
                    new OA\Property(
                        property: 'half_day_threshold_hours',
                        type: 'number',
                        example: 4.0,
                        description: 'Minimum hours to avoid half day mark (1-12)'
                    ),
                    new OA\Property(
                        property: 'effective_from',
                        type: 'string',
                        format: 'date',
                        example: '2026-01-01',
                        description: 'Date from which this policy is effective'
                    ),
                ]
            )
        ),
        tags: ['Attendance Policies'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Attendance policy created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Attendance policy created successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StorePolicyRequest $request): JsonResponse
    {
        try {
            $data               = $request->validated();
            $data['created_by'] = $request->auth_user->id;
            $policy             = $this->policyService->create($data);
            return $this->createdResponse(
                new AttendancePolicyResource($policy),
                'Attendance policy created successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/attendance/policies/{id}',
        summary: 'Get attendance policy by ID',
        security: [['bearerAuth' => []]],
        tags: ['Attendance Policies'],
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
                description: 'Attendance policy retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Attendance policy retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Attendance policy not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $policy = $this->policyService->findOrFail(
                $id,
                ['*'],
                ['createdBy']
            );
            return $this->successResponse(
                new AttendancePolicyResource($policy),
                'Attendance policy retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Attendance policy not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Put(
        path: '/api/v1/attendance/policies/{id}',
        summary: 'Update attendance policy',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated Policy'),
                    new OA\Property(property: 'grace_period_minutes', type: 'integer', example: 15),
                    new OA\Property(property: 'late_count_threshold', type: 'integer', example: 10),
                    new OA\Property(property: 'late_threshold_deduction_days', type: 'number', example: 1.0),
                    new OA\Property(property: 'absent_deduction_per_day', type: 'number', example: 1.0),
                    new OA\Property(property: 'half_day_threshold_hours', type: 'number', example: 4.0),
                    new OA\Property(property: 'effective_from', type: 'string', format: 'date', example: '2026-01-01'),
                ]
            )
        ),
        tags: ['Attendance Policies'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Attendance policy updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Attendance policy not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdatePolicyRequest $request, int $id): JsonResponse
    {
        try {
            $policy = $this->policyService->update($id, $request->validated());
            return $this->successResponse(
                new AttendancePolicyResource($policy),
                'Attendance policy updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Attendance policy not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/attendance/policies/{id}',
        summary: 'Delete attendance policy',
        security: [['bearerAuth' => []]],
        tags: ['Attendance Policies'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Attendance policy deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Attendance policy not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->policyService->delete($id);
            return $this->noContentResponse();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Attendance policy not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/attendance/policies/assign',
        summary: 'Assign attendance policy to an employee',
        description: 'Closes current active policy assignment and creates a new one from the effective date',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id', 'attendance_policy_id', 'effective_from'],
                properties: [
                    new OA\Property(
                        property: 'user_id',
                        type: 'integer',
                        example: 5,
                        description: 'ID of the employee'
                    ),
                    new OA\Property(
                        property: 'attendance_policy_id',
                        type: 'integer',
                        example: 1,
                        description: 'ID of the policy to assign'
                    ),
                    new OA\Property(
                        property: 'effective_from',
                        type: 'string',
                        format: 'date',
                        example: '2026-01-01',
                        description: 'Date from which this policy assignment is effective'
                    ),
                ]
            )
        ),
        tags: ['Attendance Policies'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Policy assigned successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Policy assigned successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function assign(AssignPolicyRequest $request): JsonResponse
    {
        try {
            $assignment = $this->policyService->assignPolicy(
                $request->validated(),
                $request->auth_user->id
            );
            return $this->createdResponse(
                $assignment,
                'Policy assigned successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/attendance/policies/user/{userId}',
        summary: 'Get current active attendance policy for an employee',
        security: [['bearerAuth' => []]],
        tags: ['Attendance Policies'],
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
                description: 'Active policy retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Active policy retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'No active policy found for this user'),
        ]
    )]
    public function getUserPolicy(int $userId): JsonResponse
    {
        try {
            $policy = $this->policyService->getActivePolicyForUser($userId);
            if (!$policy) {
                return $this->notFoundResponse('No active policy found for this user');
            }
            return $this->successResponse(
                new AttendancePolicyResource($policy),
                'Active policy retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/attendance/policies/user/{userId}/history',
        summary: 'Get full policy assignment history for an employee',
        security: [['bearerAuth' => []]],
        tags: ['Attendance Policies'],
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
                description: 'Policy assignment history retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Policy assignment history retrieved successfully'),
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
    public function getUserPolicyHistory(int $userId): JsonResponse
    {
        try {
            $history = $this->policyService->getAssignmentHistory($userId);
            return $this->successResponse(
                $history,
                'Policy assignment history retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
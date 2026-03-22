<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Http\Resources\ShiftResource;
use App\Services\ShiftService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ShiftController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ShiftService $shiftService
    ) {}

    #[OA\Get(
        path: '/api/v1/shifts',
        summary: 'List all shifts',
        security: [['bearerAuth' => []]],
        tags: ['Shifts'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Shifts retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Shifts retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Day Shift'),
                                    new OA\Property(property: 'start_time', type: 'string', example: '09:00:00'),
                                    new OA\Property(property: 'end_time', type: 'string', example: '18:00:00'),
                                    new OA\Property(property: 'cross_midnight', type: 'boolean', example: false),
                                    new OA\Property(property: 'working_hours', type: 'number', example: 9.0),
                                    new OA\Property(property: 'is_fixed', type: 'boolean', example: true),
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
            $shifts = $this->shiftService->getAllShifts();
            return $this->successResponse(
                ShiftResource::collection($shifts),
                'Shifts retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/shifts',
        summary: 'Create a new shift',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'start_time', 'end_time', 'working_hours'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Morning Shift'),
                    new OA\Property(
                        property: 'start_time',
                        type: 'string',
                        example: '09:00',
                        description: 'Format: HH:MM'
                    ),
                    new OA\Property(
                        property: 'end_time',
                        type: 'string',
                        example: '18:00',
                        description: 'Format: HH:MM'
                    ),
                    new OA\Property(
                        property: 'cross_midnight',
                        type: 'boolean',
                        example: false,
                        description: 'True if shift spans two calendar days'
                    ),
                    new OA\Property(
                        property: 'working_hours',
                        type: 'number',
                        example: 9.0,
                        description: 'Total working hours per shift'
                    ),
                    new OA\Property(
                        property: 'is_fixed',
                        type: 'boolean',
                        example: true,
                        description: 'True if this is a fixed permanent shift'
                    ),
                ]
            )
        ),
        tags: ['Shifts'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Shift created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Shift created successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreShiftRequest $request): JsonResponse
    {
        try {
            $shift = $this->shiftService->create($request->validated());
            return $this->createdResponse(
                new ShiftResource($shift),
                'Shift created successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/shifts/{id}',
        summary: 'Get shift by ID',
        security: [['bearerAuth' => []]],
        tags: ['Shifts'],
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
                description: 'Shift retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Shift retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Shift not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $shift = $this->shiftService->findOrFail($id);
            return $this->successResponse(
                new ShiftResource($shift),
                'Shift retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Shift not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Put(
        path: '/api/v1/shifts/{id}',
        summary: 'Update shift details',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated Shift'),
                    new OA\Property(property: 'start_time', type: 'string', example: '08:00'),
                    new OA\Property(property: 'end_time', type: 'string', example: '17:00'),
                    new OA\Property(property: 'cross_midnight', type: 'boolean', example: false),
                    new OA\Property(property: 'working_hours', type: 'number', example: 9.0),
                    new OA\Property(property: 'is_fixed', type: 'boolean', example: true),
                ]
            )
        ),
        tags: ['Shifts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Shift updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin only'),
            new OA\Response(response: 404, description: 'Shift not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateShiftRequest $request, int $id): JsonResponse
    {
        try {
            $shift = $this->shiftService->update($id, $request->validated());
            return $this->successResponse(
                new ShiftResource($shift),
                'Shift updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Shift not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/shifts/{id}',
        summary: 'Delete shift permanently',
        security: [['bearerAuth' => []]],
        tags: ['Shifts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Shift deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — Super Admin only'),
            new OA\Response(response: 404, description: 'Shift not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->shiftService->delete($id);
            return $this->noContentResponse();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Shift not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/shifts/list/fixed',
        summary: 'Get all fixed shifts',
        security: [['bearerAuth' => []]],
        tags: ['Shifts'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Fixed shifts retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Fixed shifts retrieved successfully'),
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
    public function getFixedShifts(): JsonResponse
    {
        try {
            $shifts = $this->shiftService->getFixedShifts();
            return $this->successResponse(
                ShiftResource::collection($shifts),
                'Fixed shifts retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/shifts/list/rotating',
        summary: 'Get all rotating shifts',
        security: [['bearerAuth' => []]],
        tags: ['Shifts'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Rotating shifts retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Rotating shifts retrieved successfully'),
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
    public function getRotatingShifts(): JsonResponse
    {
        try {
            $shifts = $this->shiftService->getRotatingShifts();
            return $this->successResponse(
                ShiftResource::collection($shifts),
                'Rotating shifts retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
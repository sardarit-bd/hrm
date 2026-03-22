<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\HolidayResource;
use App\Services\HolidayService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class HolidayController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected HolidayService $holidayService
    ) {}

    #[OA\Get(
        path: '/api/v1/holidays',
        summary: 'List all holidays for a given year',
        security: [['bearerAuth' => []]],
        tags: ['Holidays'],
        parameters: [
            new OA\Parameter(
                name: 'year',
                in: 'query',
                required: false,
                description: 'Year to get holidays for — defaults to current year',
                schema: new OA\Schema(type: 'integer', example: 2026)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Holidays retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Holidays retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Eid ul-Fitr'),
                                    new OA\Property(property: 'date', type: 'string', format: 'date'),
                                    new OA\Property(property: 'is_recurring', type: 'boolean', example: false),
                                    new OA\Property(property: 'is_today', type: 'boolean', example: false),
                                    new OA\Property(property: 'is_upcoming', type: 'boolean', example: true),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $year     = $request->integer('year', now()->year);
            $holidays = $this->holidayService->getByYear($year);
            return $this->successResponse(
                HolidayResource::collection($holidays),
                'Holidays retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/holidays',
        summary: 'Create a new holiday',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'date'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Eid ul-Fitr',
                        description: 'Holiday name'
                    ),
                    new OA\Property(
                        property: 'date',
                        type: 'string',
                        format: 'date',
                        example: '2026-03-31',
                        description: 'Holiday date'
                    ),
                    new OA\Property(
                        property: 'is_recurring',
                        type: 'boolean',
                        example: false,
                        description: 'True if this holiday recurs every year'
                    ),
                ]
            )
        ),
        tags: ['Holidays'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Holiday created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Holiday created successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name'         => ['required', 'string', 'max:255'],
                'date'         => ['required', 'date'],
                'is_recurring' => ['sometimes', 'boolean'],
            ]);
            $holiday = $this->holidayService->create($request->validated());
            return $this->createdResponse(
                new HolidayResource($holiday),
                'Holiday created successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/holidays/{id}',
        summary: 'Get holiday by ID',
        security: [['bearerAuth' => []]],
        tags: ['Holidays'],
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
                description: 'Holiday retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Holiday retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Holiday not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $holiday = $this->holidayService->findOrFail($id);
            return $this->successResponse(
                new HolidayResource($holiday),
                'Holiday retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Holiday not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Put(
        path: '/api/v1/holidays/{id}',
        summary: 'Update holiday details',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated Holiday Name'),
                    new OA\Property(property: 'date', type: 'string', format: 'date'),
                    new OA\Property(property: 'is_recurring', type: 'boolean', example: true),
                ]
            )
        ),
        tags: ['Holidays'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Holiday updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 404, description: 'Holiday not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'name'         => ['sometimes', 'string', 'max:255'],
                'date'         => ['sometimes', 'date'],
                'is_recurring' => ['sometimes', 'boolean'],
            ]);
            $holiday = $this->holidayService->update($id, $request->validated());
            return $this->successResponse(
                new HolidayResource($holiday),
                'Holiday updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Holiday not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/holidays/{id}',
        summary: 'Delete holiday permanently',
        security: [['bearerAuth' => []]],
        tags: ['Holidays'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Holiday deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 404, description: 'Holiday not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->holidayService->delete($id);
            return $this->noContentResponse();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Holiday not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/holidays/upcoming',
        summary: 'Get upcoming holidays',
        security: [['bearerAuth' => []]],
        tags: ['Holidays'],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                description: 'Number of upcoming holidays to return — defaults to 5',
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Upcoming holidays retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Upcoming holidays retrieved successfully'),
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
    public function upcoming(Request $request): JsonResponse
    {
        try {
            $limit    = $request->integer('limit', 5);
            $holidays = $this->holidayService->getUpcoming($limit);
            return $this->successResponse(
                HolidayResource::collection($holidays),
                'Upcoming holidays retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
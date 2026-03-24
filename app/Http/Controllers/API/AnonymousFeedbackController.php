<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\StoreFeedbackRequest;
use App\Http\Resources\AnonymousFeedbackResource;
use App\Services\AnonymousFeedbackService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AnonymousFeedbackController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AnonymousFeedbackService $feedbackService
    ) {}

    #[OA\Post(
        path: '/api/v1/feedback',
        summary: 'Submit anonymous feedback',
        description: 'Any authenticated employee can submit feedback. No user identity is stored — complete anonymity guaranteed.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['category', 'message', 'sentiment'],
                properties: [
                    new OA\Property(
                        property: 'category',
                        type: 'string',
                        enum: ['work_environment', 'management', 'process', 'compensation', 'other'],
                        example: 'management',
                        description: 'Category of feedback'
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'The management team has been very supportive this quarter.',
                        description: 'Feedback message — minimum 10 characters, maximum 1000'
                    ),
                    new OA\Property(
                        property: 'sentiment',
                        type: 'string',
                        enum: ['positive', 'neutral', 'negative'],
                        example: 'positive',
                        description: 'Overall sentiment of the feedback'
                    ),
                ]
            )
        ),
        tags: ['Feedback'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Feedback submitted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Feedback submitted successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'category', type: 'string', example: 'management'),
                                new OA\Property(property: 'sentiment', type: 'string', example: 'positive'),
                                new OA\Property(property: 'quarter', type: 'string', example: '2026-Q1'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreFeedbackRequest $request): JsonResponse
    {
        try {
            $feedback = $this->feedbackService->submit($request->validated());
            return $this->createdResponse(
                new AnonymousFeedbackResource($feedback),
                'Feedback submitted successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/feedback',
        summary: 'List all feedback with filters and pagination',
        security: [['bearerAuth' => []]],
        tags: ['Feedback'],
        parameters: [
            new OA\Parameter(
                name: 'category',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['work_environment', 'management', 'process', 'compensation', 'other']
                )
            ),
            new OA\Parameter(
                name: 'sentiment',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['positive', 'neutral', 'negative']
                )
            ),
            new OA\Parameter(
                name: 'quarter',
                in: 'query',
                required: false,
                description: 'Filter by quarter — format: Y-Q e.g. 2026-Q1',
                schema: new OA\Schema(type: 'string', example: '2026-Q1')
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
                description: 'Feedbacks retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Feedbacks retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(
                                        property: 'category',
                                        type: 'string',
                                        enum: ['work_environment', 'management', 'process', 'compensation', 'other']
                                    ),
                                    new OA\Property(property: 'message', type: 'string'),
                                    new OA\Property(
                                        property: 'sentiment',
                                        type: 'string',
                                        enum: ['positive', 'neutral', 'negative']
                                    ),
                                    new OA\Property(property: 'quarter', type: 'string', example: '2026-Q1'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date'),
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
            $filters   = $request->only(['category', 'sentiment', 'quarter']);
            $perPage   = $request->integer('per_page', 15);
            $feedbacks = $this->feedbackService->getPaginatedFeedbacks($filters, $perPage);

            return $this->paginatedResponse(
                AnonymousFeedbackResource::collection($feedbacks),
                'Feedbacks retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/feedback/summary/quarter/{quarter}',
        summary: 'Get feedback summary grouped by sentiment for a quarter',
        security: [['bearerAuth' => []]],
        tags: ['Feedback'],
        parameters: [
            new OA\Parameter(
                name: 'quarter',
                in: 'path',
                required: true,
                description: 'Quarter in Y-Q format e.g. 2026-Q1',
                schema: new OA\Schema(type: 'string', example: '2026-Q1')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Feedback summary retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Feedback summary retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total', type: 'integer', example: 50),
                                new OA\Property(property: 'positive', type: 'integer', example: 30),
                                new OA\Property(property: 'neutral', type: 'integer', example: 12),
                                new OA\Property(property: 'negative', type: 'integer', example: 8),
                                new OA\Property(
                                    property: 'by_category',
                                    type: 'object',
                                    example: ['management' => 15, 'process' => 10]
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
        ]
    )]
    public function summaryByQuarter(string $quarter): JsonResponse
    {
        try {
            $summary = $this->feedbackService->getSummaryByQuarter($quarter);
            return $this->successResponse($summary, 'Feedback summary retrieved successfully');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/feedback/summary/category',
        summary: 'Get feedback summary grouped by category and sentiment',
        security: [['bearerAuth' => []]],
        tags: ['Feedback'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Feedback category summary retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Feedback category summary retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            example: [
                                'management' => ['positive' => 10, 'neutral' => 5, 'negative' => 3],
                                'process'    => ['positive' => 8, 'neutral' => 4, 'negative' => 2],
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
        ]
    )]
    public function summaryByCategory(): JsonResponse
    {
        try {
            $summary = $this->feedbackService->getSummaryByCategory();
            return $this->successResponse($summary, 'Feedback category summary retrieved successfully');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
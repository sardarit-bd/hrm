<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Channel\StoreChannelRequest;
use App\Http\Requests\Channel\UpdateChannelRequest;
use App\Http\Resources\ChannelResource;
use App\Services\ChannelService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ChannelController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ChannelService $channelService
    ) {}

    #[OA\Get(
        path: '/api/v1/channels',
        summary: 'List all channels with pagination',
        security: [['bearerAuth' => []]],
        tags: ['Channels'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'is_active',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Channels retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Channels retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Fiverr'),
                                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
                                    new OA\Property(property: 'projects_count', type: 'integer', example: 5),
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
    public function index(Request $request): JsonResponse
    {
        try {
            $filters  = $request->only(['search', 'is_active']);
            $perPage  = $request->integer('per_page', 15);
            $channels = $this->channelService->getPaginatedChannels($filters, $perPage);

            return $this->paginatedResponse(
                ChannelResource::collection($channels),
                'Channels retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Post(
        path: '/api/v1/channels',
        summary: 'Create a new channel',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Fiverr',
                        description: 'Unique channel name'
                    ),
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        example: true
                    ),
                ]
            )
        ),
        tags: ['Channels'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Channel created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Channel created successfully'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreChannelRequest $request): JsonResponse
    {
        try {
            $channel = $this->channelService->create($request->validated());

            return $this->createdResponse(
                new ChannelResource($channel),
                'Channel created successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/channels/{id}',
        summary: 'Get channel by ID with all projects',
        security: [['bearerAuth' => []]],
        tags: ['Channels'],
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
                description: 'Channel retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Channel retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Fiverr'),
                                new OA\Property(property: 'is_active', type: 'boolean', example: true),
                                new OA\Property(property: 'projects_count', type: 'integer', example: 5),
                                new OA\Property(
                                    property: 'projects',
                                    type: 'array',
                                    items: new OA\Items(type: 'object')
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Channel not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $channel = $this->channelService->getChannelWithProjects($id);

            if (!$channel) {
                return $this->notFoundResponse('Channel not found');
            }

            return $this->successResponse(
                new ChannelResource($channel),
                'Channel retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Put(
        path: '/api/v1/channels/{id}',
        summary: 'Update channel',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated Channel'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
                ]
            )
        ),
        tags: ['Channels'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Channel updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 404, description: 'Channel not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateChannelRequest $request, int $id): JsonResponse
    {
        try {
            $channel = $this->channelService->update($id, $request->validated());

            return $this->successResponse(
                new ChannelResource($channel),
                'Channel updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Channel not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Delete(
        path: '/api/v1/channels/{id}',
        summary: 'Delete channel permanently',
        description: 'Cannot delete a channel that has projects assigned to it',
        security: [['bearerAuth' => []]],
        tags: ['Channels'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Channel deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden — GM and Super Admin only'),
            new OA\Response(response: 404, description: 'Channel not found'),
            new OA\Response(response: 422, description: 'Cannot delete channel with existing projects'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $channel = $this->channelService->findOrFail($id);

            if ($channel->projects()->count() > 0) {
                return $this->errorResponse(
                    'Cannot delete channel that has projects assigned',
                    422
                );
            }

            $this->channelService->delete($id);

            return $this->noContentResponse();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Channel not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    #[OA\Get(
        path: '/api/v1/channels/active',
        summary: 'Get all active channels — for dropdowns',
        security: [['bearerAuth' => []]],
        tags: ['Channels'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Active channels retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Active channels retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Fiverr'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function getActive(): JsonResponse
    {
        try {
            $channels = $this->channelService->getActiveChannels();

            return $this->successResponse(
                ChannelResource::collection($channels),
                'Active channels retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}
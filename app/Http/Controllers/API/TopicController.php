<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\StoreTopicRequest;
use App\Http\Requests\Feedback\UpdateTopicRequest;
use App\Http\Resources\TopicResource;
use App\Services\TopicService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected TopicService $topicService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'is_active']);
            $perPage = $request->integer('per_page', 15);
            $topics  = $this->topicService->getPaginatedTopics($filters, $perPage);

            return $this->paginatedResponse(
                TopicResource::collection($topics),
                'Topics retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    public function active(): JsonResponse
    {
        try {
            $topics = $this->topicService->getActiveTopics();

            return $this->successResponse(
                TopicResource::collection($topics),
                'Active topics retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $topic = $this->topicService->findById($id);

            if (!$topic) {
                return $this->notFoundResponse('Topic not found');
            }

            return $this->successResponse(
                new TopicResource($topic),
                'Topic retrieved successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    public function store(StoreTopicRequest $request): JsonResponse
    {
        try {
            $topic = $this->topicService->create($request->validated());

            return $this->createdResponse(
                new TopicResource($topic),
                'Topic created successfully'
            );
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    public function update(UpdateTopicRequest $request, int $id): JsonResponse
    {
        try {
            $topic = $this->topicService->update($id, $request->validated());

            return $this->successResponse(
                new TopicResource($topic),
                'Topic updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFoundResponse('Topic not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $topic = $this->topicService->findOrFail($id);

            if ($topic->feedbacks()->exists()) {
                return $this->errorResponse(
                    'Cannot delete topic because feedback already exists under this topic',
                    422
                );
            }

            $this->topicService->delete($id);

            return $this->noContentResponse();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFoundResponse('Topic not found');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}

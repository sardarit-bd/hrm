<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\StoreFeedbackRequest;
use App\Http\Resources\AnonymousFeedbackResource;
use App\Services\AnonymousFeedbackService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnonymousFeedbackController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AnonymousFeedbackService $feedbackService
    ) {}

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

    public function index(Request $request): JsonResponse
    {
        try {
            $filters   = $request->only(['topic_id', 'sentiment', 'quarter']);
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

    public function summaryByQuarter(string $quarter): JsonResponse
    {
        try {
            $summary = $this->feedbackService->getSummaryByQuarter($quarter);

            return $this->successResponse($summary, 'Feedback summary retrieved successfully');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    public function summaryByTopic(): JsonResponse
    {
        try {
            $summary = $this->feedbackService->getSummaryByTopic();

            return $this->successResponse($summary, 'Feedback topic summary retrieved successfully');
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }
}

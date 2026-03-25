<?php

namespace App\Services;

use App\Repositories\AnonymousFeedbackRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class AnonymousFeedbackService extends BaseService
{
    public function __construct(
        AnonymousFeedbackRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Submit anonymous feedback
     * No user ID stored — true anonymity
     */
    public function submit(array $data): Model
    {
        $quarter = $this->getCurrentQuarter();

        $feedback = $this->repository->create([
            'topic_id'   => $data['topic_id'],
            'message'    => $data['message'],
            'sentiment'  => $data['sentiment'],
            'quarter'    => $quarter,
            'created_at' => now()->toDateString(),
        ]);

        // Invalidate summary cache
        $this->cache->forget("feedback.summary.{$quarter}");
        $this->cache->forget('feedback.summary.topic');

        $this->logInfo('Anonymous feedback submitted', [
            'topic_id'  => $data['topic_id'],
            'sentiment' => $data['sentiment'],
            'quarter'   => $quarter,
        ]);

        return $feedback->load('topic');
    }

    /**
     * Get paginated feedbacks
     */
    public function getPaginatedFeedbacks(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    /**
     * Get summary by quarter
     */
    public function getSummaryByQuarter(string $quarter): array
    {
        return $this->cache->remember(
            "feedback.summary.{$quarter}",
            fn() => $this->repository->getSummaryByQuarter($quarter),
            3600
        );
    }

    /**
     * Get summary by topic
     */
    public function getSummaryByTopic(): array
    {
        return $this->cache->remember(
            'feedback.summary.topic',
            fn() => $this->repository->getSummaryByTopic(),
            3600
        );
    }

    /**
     * Get current quarter string
     */
    private function getCurrentQuarter(): string
    {
        $now     = Carbon::now();
        $quarter = ceil($now->month / 3);
        return "{$now->year}-Q{$quarter}";
    }
}

<?php

namespace App\Services;

use App\Repositories\AnonymousFeedbackRepository;
use App\Services\CacheService;
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
            'category'   => $data['category'],
            'message'    => $data['message'],
            'sentiment'  => $data['sentiment'],
            'quarter'    => $quarter,
            'created_at' => now()->toDateString(),
        ]);

        // Invalidate summary cache
        $this->cache->forget("feedback.summary.{$quarter}");

        $this->logInfo('Anonymous feedback submitted', [
            'category'  => $data['category'],
            'sentiment' => $data['sentiment'],
            'quarter'   => $quarter,
        ]);

        return $feedback;
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
     * Get summary by category
     */
    public function getSummaryByCategory(): array
    {
        return $this->cache->remember(
            'feedback.summary.category',
            fn() => $this->repository->getSummaryByCategory(),
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
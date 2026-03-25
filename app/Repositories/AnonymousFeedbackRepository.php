<?php

namespace App\Repositories;

use App\Models\AnonymousFeedback;
use Illuminate\Pagination\LengthAwarePaginator;

class AnonymousFeedbackRepository extends BaseRepository
{
    public function __construct(AnonymousFeedback $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated feedbacks with filters
     */
    public function getPaginatedWithFilters(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model->newQuery()->with('topic');

        if (!empty($filters['topic_id'])) {
            $query->where('topic_id', $filters['topic_id']);
        }

        if (!empty($filters['sentiment'])) {
            $query->where('sentiment', $filters['sentiment']);
        }

        if (!empty($filters['quarter'])) {
            $query->where('quarter', $filters['quarter']);
        }

        return $query
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get feedback summary by quarter
     */
    public function getSummaryByQuarter(string $quarter): array
    {
        $feedbacks = $this->model
            ->with('topic:id,name,slug')
            ->where('quarter', $quarter)
            ->get();

        return [
            'total'     => $feedbacks->count(),
            'positive'  => $feedbacks->where('sentiment', 'positive')->count(),
            'neutral'   => $feedbacks->where('sentiment', 'neutral')->count(),
            'negative'  => $feedbacks->where('sentiment', 'negative')->count(),
            'by_topic'  => $feedbacks
                ->groupBy(fn($item) => $item->topic?->slug ?? 'unknown')
                ->map->count(),
        ];
    }

    /**
     * Get feedback summary by topic and sentiment
     */
    public function getSummaryByTopic(): array
    {
        return $this->model
            ->join('topics', 'topics.id', '=', 'anonymous_feedbacks.topic_id')
            ->selectRaw('topics.slug as topic_slug, anonymous_feedbacks.sentiment, count(*) as total')
            ->groupBy('topics.slug', 'anonymous_feedbacks.sentiment')
            ->get()
            ->groupBy('topic_slug')
            ->map(fn($items) => $items->pluck('total', 'sentiment'))
            ->toArray();
    }
}

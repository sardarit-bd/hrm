<?php

namespace App\Repositories;

use App\Models\AnonymousFeedback;
use Illuminate\Database\Eloquent\Collection;
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
        $query = $this->model->newQuery();

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
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
            ->where('quarter', $quarter)
            ->get();

        return [
            'total'       => $feedbacks->count(),
            'positive'    => $feedbacks->where('sentiment', 'positive')->count(),
            'neutral'     => $feedbacks->where('sentiment', 'neutral')->count(),
            'negative'    => $feedbacks->where('sentiment', 'negative')->count(),
            'by_category' => $feedbacks->groupBy('category')->map->count(),
        ];
    }

    /**
     * Get feedback summary by category
     */
    public function getSummaryByCategory(): array
    {
        return $this->model
            ->selectRaw('category, sentiment, count(*) as total')
            ->groupBy('category', 'sentiment')
            ->get()
            ->groupBy('category')
            ->map(fn($items) => $items->pluck('total', 'sentiment'))
            ->toArray();
    }
}
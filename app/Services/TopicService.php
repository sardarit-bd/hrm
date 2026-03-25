<?php

namespace App\Services;

use App\Repositories\TopicRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class TopicService extends BaseService
{
    public function __construct(
        TopicRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get active topics for feedback form
     */
    public function getActiveTopics(): Collection
    {
        return $this->cache->remember(
            'topics.active',
            fn() => $this->repository->getActive(),
            86400
        );
    }

    /**
     * Get paginated topics with filters
     */
    public function getPaginatedTopics(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->repository->getPaginated($filters, $perPage);
    }

    protected function afterCreate(Model $model): void
    {
        $this->cache->forget('topics.active');
        $this->cache->forget('feedback.summary.topic');

        $this->logInfo('Topic created', [
            'topic_id' => $model->id,
            'slug'     => $model->slug,
        ]);
    }

    protected function afterUpdate(Model $model): void
    {
        $this->cache->forget('topics.active');
        $this->cache->forget('feedback.summary.topic');

        $this->logInfo('Topic updated', [
            'topic_id' => $model->id,
        ]);
    }

    protected function afterDelete(Model $model): void
    {
        $this->cache->forget('topics.active');
        $this->cache->forget('feedback.summary.topic');

        $this->logInfo('Topic deleted', [
            'topic_id' => $model->id,
        ]);
    }
}

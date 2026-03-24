<?php

namespace App\Services;

use App\Models\Channel;
use App\Repositories\ChannelRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ChannelService extends BaseService
{
    public function __construct(
        ChannelRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get all active channels — used in dropdowns
     */
    public function getActiveChannels(): Collection
    {
        return $this->cache->remember(
            'channels.active',
            fn() => $this->repository->getActive(),
            86400
        );
    }

    /**
     * Get paginated channels with filters
     */
    public function getPaginatedChannels(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->repository->getPaginated($filters, $perPage);
    }

    /**
     * Get channel with all projects
     */
    public function getChannelWithProjects(int $id): ?Channel
    {
        return $this->repository->getWithProjects($id);
    }

    /**
     * After create — bust cache
     */
    protected function afterCreate(Model $model): void
    {
        $this->cache->forget('channels.active');
        $this->logInfo('Channel created', [
            'channel_id' => $model->id,
            'name'       => $model->name,
        ]);
    }

    /**
     * After update — bust cache
     */
    protected function afterUpdate(Model $model): void
    {
        $this->cache->forget('channels.active');
        $this->logInfo('Channel updated', [
            'channel_id' => $model->id,
        ]);
    }

    /**
     * After delete — bust cache
     */
    protected function afterDelete(Model $model): void
    {
        $this->cache->forget('channels.active');
        $this->logInfo('Channel deleted', [
            'channel_id' => $model->id,
        ]);
    }
}
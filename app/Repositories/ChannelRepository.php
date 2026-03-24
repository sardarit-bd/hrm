<?php

namespace App\Repositories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ChannelRepository extends BaseRepository
{
    public function __construct(Channel $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active channels
     */
    public function getActive(): Collection
    {
        return $this->model
            ->active()
            ->withCount('projects')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all channels paginated
     */
    public function getPaginated(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model->withCount('projects');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Get channel with projects
     */
    public function getWithProjects(int $id): ?Channel
    {
        return $this->model
            ->with(['projects.projectManager'])
            ->withCount('projects')
            ->find($id);
    }
}
<?php

namespace App\Services;

use App\Models\Project;
use App\Repositories\ProjectRepository;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService extends BaseService
{
    public function __construct(
        ProjectRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get paginated projects with filters
     */
    public function getPaginatedProjects(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    /**
     * Get projects by manager with cache
     */
    public function getProjectsByManager(int $managerId): Collection
    {
        return $this->cache->remember(
            "projects.manager.{$managerId}",
            fn() => $this->repository->getByManager($managerId),
            3600
        );
    }

    /**
     * Get ongoing projects
     */
    public function getOngoingProjects(): Collection
    {
        return $this->cache->remember(
            'projects.ongoing',
            fn() => $this->repository->getOngoing(),
            1800
        );
    }

    /**
     * Get overdue projects
     */
    public function getOverdueProjects(): Collection
    {
        return $this->repository->getOverdue();
    }

    /**
     * Get projects for a specific user
     */
    public function getProjectsForUser(int $userId): Collection
    {
        return $this->cache->remember(
            "projects.user.{$userId}",
            fn() => $this->repository->getProjectsForUser($userId),
            3600
        );
    }

    /**
     * Update project status
     */
    public function updateStatus(int $id, string $status): Model
    {
        $data = ['status' => $status];

        if ($status === 'delivered') {
            $data['delivered_date'] = now()->toDateString();
        }

        return $this->update($id, $data);
    }

    /**
     * After create hook
     */
    protected function afterCreate(Model $model): void
    {
        $this->invalidateProjectCache($model);
        $this->logInfo('Project created', [
            'project_id' => $model->id,
            'name'       => $model->name,
        ]);
    }

    /**
     * After update hook
     */
    protected function afterUpdate(Model $model): void
    {
        $this->invalidateProjectCache($model);
        $this->logInfo('Project updated', ['project_id' => $model->id]);
    }

    /**
     * After delete hook
     */
    protected function afterDelete(Model $model): void
    {
        $this->invalidateProjectCache($model);
        $this->logInfo('Project deleted', ['project_id' => $model->id]);
    }

    /**
     * Invalidate project related caches
     */
    private function invalidateProjectCache(Model $model): void
    {
        $this->cache->forget('projects.ongoing');
        $this->cache->forget("projects.manager.{$model->project_manager_id}");
    }
}
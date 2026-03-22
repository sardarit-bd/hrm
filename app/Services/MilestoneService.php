<?php

namespace App\Services;

use App\Repositories\MilestoneRepository;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class MilestoneService extends BaseService
{
    public function __construct(
        MilestoneRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get milestones by project
     */
    public function getMilestonesByProject(int $projectId): Collection
    {
        return $this->cache->remember(
            "milestones.project.{$projectId}",
            fn() => $this->repository->getByProject($projectId),
            3600
        );
    }

    /**
     * Get overdue milestones
     */
    public function getOverdueMilestones(): Collection
    {
        return $this->repository->getOverdue();
    }

    /**
     * Mark milestone as completed
     */
    public function markAsCompleted(int $id): Model
    {
        return $this->update($id, [
            'status'          => 'completed',
            'completion_date' => now()->toDateString(),
        ]);
    }

    /**
     * Mark milestone as missed
     */
    public function markAsMissed(int $id): Model
    {
        return $this->update($id, ['status' => 'missed']);
    }

    /**
     * Get total earned value for project
     */
    public function getTotalEarnedByProject(int $projectId): float
    {
        return $this->repository->getTotalValueByProject($projectId);
    }

    /**
     * After create hook
     */
    protected function afterCreate(Model $model): void
    {
        $this->cache->forget("milestones.project.{$model->project_id}");
        $this->logInfo('Milestone created', ['milestone_id' => $model->id]);
    }

    /**
     * After update hook
     */
    protected function afterUpdate(Model $model): void
    {
        $this->cache->forget("milestones.project.{$model->project_id}");
        $this->logInfo('Milestone updated', ['milestone_id' => $model->id]);
    }

    /**
     * After delete hook
     */
    protected function afterDelete(Model $model): void
    {
        $this->cache->forget("milestones.project.{$model->project_id}");
        $this->logInfo('Milestone deleted', ['milestone_id' => $model->id]);
    }
}
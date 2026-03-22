<?php

namespace App\Repositories;

use App\Models\Milestone;
use Illuminate\Database\Eloquent\Collection;

class MilestoneRepository extends BaseRepository
{
    public function __construct(Milestone $model)
    {
        parent::__construct($model);
    }

    /**
     * Get milestones by project
     */
    public function getByProject(int $projectId): Collection
    {
        return $this->model
            ->where('project_id', $projectId)
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get overdue milestones
     */
    public function getOverdue(): Collection
    {
        return $this->model
            ->with(['project'])
            ->where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get milestones by status
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model
            ->with(['project'])
            ->where('status', $status)
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get total milestone value for project
     */
    public function getTotalValueByProject(int $projectId): float
    {
        return $this->model
            ->where('project_id', $projectId)
            ->where('status', 'completed')
            ->sum('milestone_value');
    }
}
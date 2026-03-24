<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectRepository extends BaseRepository
{
    public function __construct(Project $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated projects with filters
     */
    public function getPaginatedWithFilters(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model
            ->with(['projectManager']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['project_manager_id'])) {
            $query->where(
                'project_manager_id',
                $filters['project_manager_id']
            );
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('client_name', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('start_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('deadline', '<=', $filters['to_date']);
        }

        return $query
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get projects by manager
     */
    public function getByManager(int $managerId): Collection
    {
        return $this->model
            ->with(['projectManager'])
            ->where('project_manager_id', $managerId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get ongoing projects
     */
    public function getOngoing(): Collection
    {
        return $this->model
            ->with(['projectManager'])
            ->where('status', 'ongoing')
            ->orderBy('deadline')
            ->get();
    }

    /**
     * Get overdue projects
     */
    public function getOverdue(): Collection
    {
        return $this->model
            ->with(['projectManager'])
            ->where('status', 'ongoing')
            ->where('deadline', '<', now()->toDateString())
            ->orderBy('deadline')
            ->get();
    }

    /**
     * Get projects for a specific user
     * via team project assignments
     */
    public function getProjectsForUser(int $userId): Collection
    {
        return $this->model
            ->with(['projectManager'])
            ->whereHas('teamProjectAssignments', function ($q) use ($userId) {
                $q->whereHas('projectMemberAssignments', function ($q2) use ($userId) {
                    $q2->where('user_id', $userId)
                        ->whereNull('released_at');
                });
            })
            ->orderByDesc('created_at')
            ->get();
    }
}
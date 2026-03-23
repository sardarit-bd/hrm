<?php

namespace App\Repositories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentRepository extends BaseRepository
{
    public function __construct(Department $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active departments
     */
    public function getActive(): Collection
    {
        return $this->model
            ->active()
            ->withCount(['teams', 'users'])
            ->with('manager')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get paginated departments with filters
     */
    public function getPaginatedWithFilters(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model
            ->withCount(['teams', 'users'])
            ->with('manager');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get department with teams and members
     */
    public function getWithTeamsAndMembers(int $id): ?Department
    {
        return $this->model
            ->with([
                'manager',
                'teams.leader',
                'teams.members',
            ])
            ->withCount(['teams', 'users'])
            ->find($id);
    }

    /**
     * Get departments by manager
     */
    public function getByManager(int $managerId): Collection
    {
        return $this->model
            ->where('manager_id', $managerId)
            ->withCount(['teams', 'users'])
            ->get();
    }
}
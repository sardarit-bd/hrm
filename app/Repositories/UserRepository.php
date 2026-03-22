<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findBy('email', $email);
    }

    /**
     * Find user by employee code
     */
    public function findByEmployeeCode(string $code): ?User
    {
        return $this->findBy('employee_code', $code);
    }

    /**
     * Get all active users
     */
    public function getActiveUsers(): Collection
    {
        return $this->getAllBy('status', 'active');
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role): Collection
    {
        return $this->getAllBy('role', $role);
    }

    /**
     * Get users by department
     */
    public function getByDepartment(string $department): Collection
    {
        return $this->getAllBy('department', $department);
    }

    /**
     * Get paginated users with filters
     */
    public function getPaginatedWithFilters(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model->newQuery();

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%")
                    ->orWhere('employee_code', 'like', "%{$filters['search']}%");
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get project managers
     */
    public function getProjectManagers(): Collection
    {
        return $this->getByRole('project_manager');
    }

    /**
     * Get team leaders
     */
    public function getTeamLeaders(): Collection
    {
        return $this->getByRole('team_leader');
    }

    /**
     * Get employees only
     */
    public function getEmployees(): Collection
    {
        return $this->getByRole('employee');
    }
}
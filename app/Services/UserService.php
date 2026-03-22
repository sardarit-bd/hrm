<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    public function __construct(
        UserRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get paginated users with filters
     */
    public function getPaginatedUsers(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    /**
     * Get all project managers
     */
    public function getProjectManagers(): Collection
    {
        return $this->cache->remember(
            'users.project_managers',
            fn() => $this->repository->getProjectManagers(),
            3600
        );
    }

    /**
     * Get all team leaders
     */
    public function getTeamLeaders(): Collection
    {
        return $this->cache->remember(
            'users.team_leaders',
            fn() => $this->repository->getTeamLeaders(),
            3600
        );
    }

    /**
     * Get all employees
     */
    public function getEmployees(): Collection
    {
        return $this->cache->remember(
            'users.employees',
            fn() => $this->repository->getEmployees(),
            3600
        );
    }

    /**
     * Create user with hashed password
     */
    public function create(array $data): Model
    {
        $data['password'] = Hash::make($data['password']);
        return parent::create($data);
    }

    /**
     * Update user
     */
    public function update(int $id, array $data): Model
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return parent::update($id, $data);
    }

    /**
     * Change user status
     */
    public function changeStatus(int $id, string $status): Model
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * After create hook
     * Invalidate role-based caches
     */
    protected function afterCreate(Model $model): void
    {
        $this->invalidateRoleCache($model->role);

        $this->logInfo('User created', [
            'user_id'       => $model->id,
            'employee_code' => $model->employee_code,
            'role'          => $model->role,
        ]);
    }

    /**
     * After update hook
     * Invalidate user and role caches
     */
    protected function afterUpdate(Model $model): void
    {
        $this->cache->forgetUserCache($model->id);
        $this->invalidateRoleCache($model->role);

        $this->logInfo('User updated', [
            'user_id' => $model->id,
        ]);
    }

    /**
     * After delete hook
     */
    protected function afterDelete(Model $model): void
    {
        $this->cache->forgetUserCache($model->id);
        $this->invalidateRoleCache($model->role);

        $this->logInfo('User deleted', [
            'user_id' => $model->id,
        ]);
    }

    /**
     * Invalidate role-based cache
     */
    private function invalidateRoleCache(string $role): void
    {
        match ($role) {
            'project_manager' => $this->cache->forget('users.project_managers'),
            'team_leader'     => $this->cache->forget('users.team_leaders'),
            'employee'        => $this->cache->forget('users.employees'),
            default           => null,
        };
    }
}
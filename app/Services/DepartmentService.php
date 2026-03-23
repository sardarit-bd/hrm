<?php

namespace App\Services;

use App\Models\Department;
use App\Repositories\DepartmentRepository;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentService extends BaseService
{
    public function __construct(
        DepartmentRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get all active departments with cache
     */
    public function getActiveDepartments(): Collection
    {
        return $this->cache->remember(
            'departments.active',
            fn() => $this->repository->getActive(),
            86400
        );
    }

    /**
     * Get paginated departments
     */
    public function getPaginatedDepartments(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    /**
     * Get department with full details
     */
    public function getDepartmentWithDetails(int $id): ?Department
    {
        return $this->repository->getWithTeamsAndMembers($id);
    }

    /**
     * After create hook
     */
    protected function afterCreate(Model $model): void
    {
        $this->cache->forget('departments.active');
        $this->logInfo('Department created', [
            'department_id' => $model->id,
            'name'          => $model->name,
        ]);
    }

    /**
     * After update hook
     */
    protected function afterUpdate(Model $model): void
    {
        $this->cache->forget('departments.active');
        $this->logInfo('Department updated', [
            'department_id' => $model->id,
        ]);
    }

    /**
     * After delete hook
     */
    protected function afterDelete(Model $model): void
    {
        $this->cache->forget('departments.active');
        $this->logInfo('Department deleted', [
            'department_id' => $model->id,
        ]);
    }
}
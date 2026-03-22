<?php

namespace App\Services;

use App\Repositories\LeaveTypeRepository;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class LeaveTypeService extends BaseService
{
    public function __construct(
        LeaveTypeRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get all leave types with cache
     */
    public function getAllLeaveTypes(): Collection
    {
        return $this->cache->rememberLeaveTypes(
            fn() => $this->repository->getAllOrdered()
        );
    }

    /**
     * Get paid leave types
     */
    public function getPaidLeaveTypes(): Collection
    {
        return $this->cache->remember(
            'leave_types.paid',
            fn() => $this->repository->getPaidLeaveTypes(),
            86400
        );
    }

    /**
     * Get unpaid leave types
     */
    public function getUnpaidLeaveTypes(): Collection
    {
        return $this->cache->remember(
            'leave_types.unpaid',
            fn() => $this->repository->getUnpaidLeaveTypes(),
            86400
        );
    }

    protected function afterCreate(Model $model): void
    {
        $this->invalidateLeaveTypeCache();
        $this->logInfo('Leave type created', ['id' => $model->id]);
    }

    protected function afterUpdate(Model $model): void
    {
        $this->invalidateLeaveTypeCache();
        $this->logInfo('Leave type updated', ['id' => $model->id]);
    }

    protected function afterDelete(Model $model): void
    {
        $this->invalidateLeaveTypeCache();
        $this->logInfo('Leave type deleted', ['id' => $model->id]);
    }

    private function invalidateLeaveTypeCache(): void
    {
        $this->cache->forgetLeaveTypes();
        $this->cache->forget('leave_types.paid');
        $this->cache->forget('leave_types.unpaid');
    }
}
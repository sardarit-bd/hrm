<?php

namespace App\Services;

use App\Models\Shift;
use App\Repositories\ShiftRepository;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ShiftService extends BaseService
{
    public function __construct(
        ShiftRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get all shifts with cache
     */
    public function getAllShifts(): Collection
    {
        return $this->cache->rememberShifts(
            fn() => $this->repository->getAllOrdered()
        );
    }

    /**
     * Get fixed shifts
     */
    public function getFixedShifts(): Collection
    {
        return $this->cache->remember(
            'shifts.fixed',
            fn() => $this->repository->getFixedShifts(),
            86400
        );
    }

    /**
     * Get rotating shifts
     */
    public function getRotatingShifts(): Collection
    {
        return $this->cache->remember(
            'shifts.rotating',
            fn() => $this->repository->getRotatingShifts(),
            86400
        );
    }

    /**
     * After create hook
     */
    protected function afterCreate(Model $model): void
    {
        $this->invalidateShiftCache();
        $this->logInfo('Shift created', ['shift_id' => $model->id]);
    }

    /**
     * After update hook
     */
    protected function afterUpdate(Model $model): void
    {
        $this->invalidateShiftCache();
        $this->logInfo('Shift updated', ['shift_id' => $model->id]);
    }

    /**
     * After delete hook
     */
    protected function afterDelete(Model $model): void
    {
        $this->invalidateShiftCache();
        $this->logInfo('Shift deleted', ['shift_id' => $model->id]);
    }

    /**
     * Invalidate all shift caches
     */
    private function invalidateShiftCache(): void
    {
        $this->cache->forgetShifts();
        $this->cache->forget('shifts.fixed');
        $this->cache->forget('shifts.rotating');
    }
}
<?php

namespace App\Services;

use App\Repositories\HolidayRepository;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class HolidayService extends BaseService
{
    public function __construct(
        HolidayRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get holidays by year with cache
     */
    public function getByYear(int $year): Collection
    {
        return $this->cache->rememberHolidays(
            $year,
            fn() => $this->repository->getByYear($year)
        );
    }

    /**
     * Get upcoming holidays
     */
    public function getUpcoming(int $limit = 5): Collection
    {
        return $this->cache->remember(
            'holidays.upcoming',
            fn() => $this->repository->getUpcoming($limit),
            86400
        );
    }

    /**
     * Check if date is holiday
     */
    public function isHoliday(string $date): bool
    {
        $year     = date('Y', strtotime($date));
        $holidays = $this->getByYear((int) $year);

        return $holidays->contains(
            fn($h) => $h->date->format('Y-m-d') === $date
        );
    }

    /**
     * Get holidays in date range
     */
    public function getInRange(string $from, string $to): Collection
    {
        return $this->repository->getInRange($from, $to);
    }

    /**
     * After create hook
     */
    protected function afterCreate(Model $model): void
    {
        $this->invalidateHolidayCache($model);
        $this->logInfo('Holiday created', ['holiday_id' => $model->id]);
    }

    /**
     * After update hook
     */
    protected function afterUpdate(Model $model): void
    {
        $this->invalidateHolidayCache($model);
        $this->logInfo('Holiday updated', ['holiday_id' => $model->id]);
    }

    /**
     * After delete hook
     */
    protected function afterDelete(Model $model): void
    {
        $this->invalidateHolidayCache($model);
        $this->logInfo('Holiday deleted', ['holiday_id' => $model->id]);
    }

    /**
     * Invalidate holiday cache
     */
    private function invalidateHolidayCache(Model $model): void
    {
        $year = $model->date->year;
        $this->cache->forgetHolidays($year);
        $this->cache->forget('holidays.upcoming');
    }
}
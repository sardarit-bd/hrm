<?php

namespace App\Repositories;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Collection;

class HolidayRepository extends BaseRepository
{
    public function __construct(Holiday $model)
    {
        parent::__construct($model);
    }

    /**
     * Get holidays by year
     */
    public function getByYear(int $year): Collection
    {
        return $this->model
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();
    }

    /**
     * Get upcoming holidays
     */
    public function getUpcoming(int $limit = 5): Collection
    {
        return $this->model
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if a date is a holiday
     */
    public function isHoliday(string $date): bool
    {
        return $this->model
            ->where('date', $date)
            ->exists();
    }

    /**
     * Get holidays in date range
     */
    public function getInRange(string $from, string $to): Collection
    {
        return $this->model
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get();
    }

    /**
     * Get recurring holidays
     */
    public function getRecurring(): Collection
    {
        return $this->model
            ->where('is_recurring', true)
            ->orderBy('date')
            ->get();
    }
}
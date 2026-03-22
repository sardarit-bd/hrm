<?php

namespace App\Repositories;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Collection;

class ShiftRepository extends BaseRepository
{
    public function __construct(Shift $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all fixed shifts
     */
    public function getFixedShifts(): Collection
    {
        return $this->model
            ->where('is_fixed', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all rotating shifts
     */
    public function getRotatingShifts(): Collection
    {
        return $this->model
            ->where('is_fixed', false)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get shifts ordered by start time
     */
    public function getAllOrderedByTime(): Collection
    {
        return $this->model
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Check if shift has active roster assignments
     */
    public function hasActiveRosterAssignments(int $shiftId): bool
    {
        return $this->model
            ->where('id', $shiftId)
            ->whereHas('rosterAssignments', function ($q) {
                $q->whereNull('effective_to');
            })
            ->exists();
    }
}
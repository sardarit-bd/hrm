<?php

namespace App\Repositories;

use App\Models\RosterAssignment;
use Illuminate\Database\Eloquent\Collection;

class RosterAssignmentRepository extends BaseRepository
{
    public function __construct(RosterAssignment $model)
    {
        parent::__construct($model);
    }

    /**
     * Get active roster for user on a specific date
     */
    public function getActiveRosterForUser(
        int $userId,
        string $date
    ): ?RosterAssignment {
        return $this->model
            ->with(['shift'])
            ->where('user_id', $userId)
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            })
            ->latest('effective_from')
            ->first();
    }

    /**
     * Get current active roster for user
     */
    public function getCurrentRoster(int $userId): ?RosterAssignment
    {
        return $this->model
            ->with(['shift'])
            ->where('user_id', $userId)
            ->whereNull('effective_to')
            ->latest('effective_from')
            ->first();
    }

    /**
     * Get all roster history for user
     */
    public function getRosterHistory(int $userId): Collection
    {
        return $this->model
            ->with(['shift', 'assignedBy'])
            ->where('user_id', $userId)
            ->orderByDesc('effective_from')
            ->get();
    }

    /**
     * Close current active roster
     */
    public function closeCurrentRoster(
        int $userId,
        string $effectiveTo
    ): bool {
        return $this->model
            ->where('user_id', $userId)
            ->whereNull('effective_to')
            ->update(['effective_to' => $effectiveTo]);
    }

    /**
     * Get all users on a specific shift
     */
    public function getUsersByShift(int $shiftId): Collection
    {
        return $this->model
            ->with(['user'])
            ->where('shift_id', $shiftId)
            ->whereNull('effective_to')
            ->get();
    }
}
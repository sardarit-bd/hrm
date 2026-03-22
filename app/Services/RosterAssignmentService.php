<?php

namespace App\Services;

use App\Models\RosterAssignment;
use App\Repositories\RosterAssignmentRepository;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RosterAssignmentService extends BaseService
{
    public function __construct(
        RosterAssignmentRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Assign roster to employee
     * Closes current active roster before creating new one
     */
    public function assignRoster(array $data, int $assignedBy): Model
    {
        return $this->transaction(function () use ($data, $assignedBy) {

            // Close existing active roster
            // effective_to = one day before new effective_from
            $newEffectiveFrom = Carbon::parse($data['effective_from']);
            $effectiveTo      = $newEffectiveFrom->copy()->subDay()->format('Y-m-d');

            $this->repository->closeCurrentRoster(
                $data['user_id'],
                $effectiveTo
            );

            // Invalidate cache for this user
            $this->cache->forgetActiveRoster($data['user_id']);

            // Create new roster
            $roster = $this->repository->create([
                'user_id'        => $data['user_id'],
                'shift_id'       => $data['shift_id'],
                'weekend_days'   => $data['weekend_days'],
                'effective_from' => $data['effective_from'],
                'effective_to'   => null,
                'assigned_by'    => $assignedBy,
            ]);

            $this->logInfo('Roster assigned', [
                'user_id'  => $data['user_id'],
                'shift_id' => $data['shift_id'],
            ]);

            return $roster->load(['user', 'shift', 'assignedBy']);
        });
    }

    /**
     * Get active roster for user
     * Uses cache
     */
    public function getActiveRoster(int $userId): ?RosterAssignment
    {
        return $this->cache->rememberActiveRoster(
            $userId,
            fn() => $this->repository->getCurrentRoster($userId)
        );
    }

    /**
     * Get active roster for user on a specific date
     */
    public function getActiveRosterForDate(
        int $userId,
        string $date
    ): ?RosterAssignment {
        return $this->repository->getActiveRosterForUser($userId, $date);
    }

    /**
     * Get roster history for user
     */
    public function getRosterHistory(int $userId): Collection
    {
        return $this->repository->getRosterHistory($userId);
    }

    /**
     * Check if a given date is a weekend for user
     */
    public function isWeekend(int $userId, string $date): bool
    {
        $roster = $this->getActiveRosterForDate($userId, $date);

        if (!$roster) {
            return false;
        }

        $dayName = strtolower(Carbon::parse($date)->format('l'));

        return in_array($dayName, $roster->weekend_days ?? []);
    }

    /**
     * Get users on a specific shift
     */
    public function getUsersByShift(int $shiftId): Collection
    {
        return $this->repository->getUsersByShift($shiftId);
    }

    /**
     * After update hook
     */
    protected function afterUpdate(Model $model): void
    {
        $this->cache->forgetActiveRoster($model->user_id);
    }

    /**
     * After delete hook
     */
    protected function afterDelete(Model $model): void
    {
        $this->cache->forgetActiveRoster($model->user_id);
    }
}
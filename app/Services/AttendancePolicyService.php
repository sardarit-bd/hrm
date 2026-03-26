<?php

namespace App\Services;

use App\Models\AttendancePolicy;
use App\Repositories\AttendancePolicyRepository;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AttendancePolicyService extends BaseService
{
    public function __construct(
        AttendancePolicyRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get all active policies with cache
     */
    public function getActivePolicies(): Collection
    {
        $cacheKey = 'attendance_policies.active';

        $policies = $this->cache->remember(
            $cacheKey,
            fn() => $this->repository->getActivePolicies(),
            86400
        );

        if ($policies instanceof Collection) {
            return $policies;
        }

        // Recover from stale/invalid serialized cache values (e.g. __PHP_Incomplete_Class).
        $this->cache->forget($cacheKey);

        $freshPolicies = $this->repository->getActivePolicies();
        $this->cache->put($cacheKey, $freshPolicies, 86400);

        return $freshPolicies;
    }

    /**
     * Get active policy for user with cache
     */
    public function getActivePolicyForUser(int $userId): ?AttendancePolicy
    {
        $cacheKey = "policy.active.user.{$userId}";

        $policy = $this->cache->rememberActivePolicy(
            $userId,
            fn() => $this->repository->getCurrentPolicyForUser($userId)
        );

        if ($policy === null || $policy instanceof AttendancePolicy) {
            return $policy;
        }

        // Recover from stale/invalid serialized cache values (e.g. __PHP_Incomplete_Class).
        $this->cache->forgetActivePolicy($userId);

        $freshPolicy = $this->repository->getCurrentPolicyForUser($userId);
        $this->cache->put($cacheKey, $freshPolicy, 86400);

        return $freshPolicy;
    }

    /**
     * Get active policy for user on a specific date
     */
    public function getActivePolicyForDate(
        int $userId,
        string $date
    ): ?AttendancePolicy {
        return $this->repository->getActivePolicyForUser($userId, $date);
    }

    /**
     * Assign policy to employee
     * Closes current active assignment before creating new one
     */
    public function assignPolicy(array $data, int $assignedBy): Model
    {
        return $this->transaction(function () use ($data, $assignedBy) {

            // Close existing active assignment
            $newEffectiveFrom = Carbon::parse($data['effective_from']);
            $effectiveTo      = $newEffectiveFrom->copy()->subDay()->format('Y-m-d');

            $this->repository->closeCurrentAssignment(
                $data['user_id'],
                $effectiveTo
            );

            // Invalidate cache
            $this->cache->forgetActivePolicy($data['user_id']);

            // Create new assignment
            $assignment = $this->repository->createAssignment([
                'user_id'               => $data['user_id'],
                'attendance_policy_id'  => $data['attendance_policy_id'],
                'effective_from'        => $data['effective_from'],
                'effective_to'          => null,
                'assigned_by'           => $assignedBy,
            ]);

            $this->logInfo('Policy assigned', [
                'user_id'   => $data['user_id'],
                'policy_id' => $data['attendance_policy_id'],
            ]);

            return $assignment->load(['attendancePolicy']);
        });
    }

    /**
     * Get policy assignment history for user
     */
    public function getAssignmentHistory(int $userId): Collection
    {
        return $this->repository->getAssignmentHistory($userId);
    }

    /**
     * After create hook
     */
    protected function afterCreate(Model $model): void
    {
        $this->cache->forget('attendance_policies.active');
        $this->logInfo('Policy created', ['policy_id' => $model->id]);
    }

    /**
     * After update hook
     */
    protected function afterUpdate(Model $model): void
    {
        $this->cache->forget('attendance_policies.active');
        $this->logInfo('Policy updated', ['policy_id' => $model->id]);
    }

    /**
     * After delete hook
     */
    protected function afterDelete(Model $model): void
    {
        $this->cache->forget('attendance_policies.active');
        $this->logInfo('Policy deleted', ['policy_id' => $model->id]);
    }
}

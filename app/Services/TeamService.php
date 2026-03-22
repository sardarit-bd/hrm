<?php

namespace App\Services;

use App\Models\Team;
use App\Repositories\TeamRepository;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class TeamService extends BaseService
{
    public function __construct(
        TeamRepository $repository,
        CacheService $cache
    ) {
        parent::__construct($repository, $cache);
    }

    /**
     * Get paginated teams with filters
     */
    public function getPaginatedTeams(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    /**
     * Create team
     */
    public function create(array $data): Model
    {
        return parent::create($data);
    }

    /**
     * Add member to team
     */
    public function addMember(
        int $teamId,
        int $userId,
        string $joinedAt
    ): Model {
        // Check if already a member
        if ($this->repository->isMember($teamId, $userId)) {
            throw new \Exception('User is already an active member of this team');
        }

        $member = $this->repository->addMember($teamId, $userId, $joinedAt);

        $this->cache->forget("team.members.{$teamId}");

        $this->logInfo('Member added to team', [
            'team_id' => $teamId,
            'user_id' => $userId,
        ]);

        return $member;
    }

    /**
     * Remove member from team
     */
    public function removeMember(int $teamId, int $userId): bool
    {
        if (!$this->repository->isMember($teamId, $userId)) {
            throw new \Exception('User is not an active member of this team');
        }

        $result = $this->repository->removeMember($teamId, $userId);

        $this->cache->forget("team.members.{$teamId}");

        $this->logInfo('Member removed from team', [
            'team_id' => $teamId,
            'user_id' => $userId,
        ]);

        return $result;
    }

    /**
     * Assign team to project
     */
    public function assignToProject(
        int $teamId,
        int $projectId,
        int $assignedBy,
        string $assignedAt
    ): Model {
        if ($this->repository->isAssignedToProject($teamId, $projectId)) {
            throw new \Exception(
                'Team is already assigned to this project'
            );
        }

        $assignment = $this->repository->assignToProject(
            $teamId,
            $projectId,
            $assignedBy,
            $assignedAt
        );

        $this->cache->forget("projects.user.{$assignedBy}");

        $this->logInfo('Team assigned to project', [
            'team_id'    => $teamId,
            'project_id' => $projectId,
        ]);

        return $assignment;
    }

    /**
     * Assign member to project
     */
    public function assignMemberToProject(
        array $data,
        int $assignedBy
    ): Model {
        $assignment = $this->repository->assignMemberToProject([
            'team_project_assignment_id' => $data['team_project_assignment_id'],
            'user_id'                    => $data['user_id'],
            'assigned_by'                => $assignedBy,
            'assigned_at'                => $data['assigned_at'],
        ]);

        $this->cache->forget("projects.user.{$data['user_id']}");

        $this->logInfo('Member assigned to project', [
            'user_id'                    => $data['user_id'],
            'team_project_assignment_id' => $data['team_project_assignment_id'],
        ]);

        return $assignment;
    }

    /**
     * Release member from project
     */
    public function releaseMemberFromProject(
        int $teamProjectAssignmentId,
        int $userId
    ): bool {
        $result = $this->repository->releaseMemberFromProject(
            $teamProjectAssignmentId,
            $userId
        );

        $this->cache->forget("projects.user.{$userId}");

        $this->logInfo('Member released from project', [
            'user_id'                    => $userId,
            'team_project_assignment_id' => $teamProjectAssignmentId,
        ]);

        return $result;
    }

    /**
     * Get project members
     */
    public function getProjectMembers(
        int $teamProjectAssignmentId
    ): Collection {
        return $this->cache->remember(
            "project.members.{$teamProjectAssignmentId}",
            fn() => $this->repository->getProjectMembers(
                $teamProjectAssignmentId
            ),
            3600
        );
    }

    /**
     * Get teams by leader
     */
    public function getTeamsByLeader(int $leaderId): Collection
    {
        return $this->cache->remember(
            "teams.leader.{$leaderId}",
            fn() => $this->repository->getTeamsByLeader($leaderId),
            3600
        );
    }

    /**
     * After create hook
     */
    protected function afterCreate(Model $model): void
    {
        $this->logInfo('Team created', ['team_id' => $model->id]);
    }

    /**
     * After update hook
     */
    protected function afterUpdate(Model $model): void
    {
        $this->cache->forget("teams.leader.{$model->leader_id}");
        $this->logInfo('Team updated', ['team_id' => $model->id]);
    }

    /**
     * After delete hook
     */
    protected function afterDelete(Model $model): void
    {
        $this->cache->forget("teams.leader.{$model->leader_id}");
        $this->logInfo('Team deleted', ['team_id' => $model->id]);
    }
}
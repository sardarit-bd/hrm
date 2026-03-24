<?php

namespace App\Repositories;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamProjectAssignment;
use App\Models\ProjectMemberAssignment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TeamRepository extends BaseRepository
{
    public function __construct(Team $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated teams with filters
     */
    public function getPaginatedWithFilters(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model
            ->withCount('teamMembers')
            ->with(['leader', 'members']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['has_leader'])) {
            if ($filters['has_leader']) {
                $query->whereNotNull('leader_id');
            } else {
                $query->whereNull('leader_id');
            }
        }

        return $query
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get teams led by a specific user
     */
    public function getTeamsByLeader(int $leaderId): Collection
    {
        return $this->model
            ->with(['leader', 'members'])
            ->where('leader_id', $leaderId)
            ->get();
    }

    /**
     * Add member to team
     */
    public function addMember(
        int $teamId,
        int $userId,
        string $joinedAt
    ): TeamMember {
        return TeamMember::firstOrCreate(
            [
                'team_id' => $teamId,
                'user_id' => $userId,
            ],
            [
                'joined_at' => $joinedAt,
                'left_at'   => null,
            ]
        );
    }

    /**
     * Remove member from team
     */
    public function removeMember(int $teamId, int $userId): bool
    {
        return TeamMember::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->update(['left_at' => now()->toDateString()]);
    }

    /**
     * Check if user is already active member of team
     */
    public function isMember(int $teamId, int $userId): bool
    {
        return TeamMember::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->exists();
    }

    /**
     * Assign team to project
     */
    public function assignToProject(
        int $teamId,
        int $projectId,
        int $assignedBy,
        string $assignedAt
    ): TeamProjectAssignment {
        return TeamProjectAssignment::firstOrCreate(
            [
                'team_id'    => $teamId,
                'project_id' => $projectId,
            ],
            [
                'assigned_by' => $assignedBy,
                'assigned_at' => $assignedAt,
            ]
        );
    }

    /**
     * Check if team is already assigned to project
     */
    public function isAssignedToProject(
        int $teamId,
        int $projectId
    ): bool {
        return TeamProjectAssignment::where('team_id', $teamId)
            ->where('project_id', $projectId)
            ->exists();
    }

    /**
     * Assign member to project
     */
    public function assignMemberToProject(array $data): ProjectMemberAssignment
    {
        return ProjectMemberAssignment::firstOrCreate(
            [
                'team_project_assignment_id' => $data['team_project_assignment_id'],
                'user_id'                    => $data['user_id'],
            ],
            [
                'assigned_by' => $data['assigned_by'],
                'assigned_at' => $data['assigned_at'],
                'released_at' => null,
            ]
        );
    }

    /**
     * Release member from project
     */
    public function releaseMemberFromProject(
        int $teamProjectAssignmentId,
        int $userId
    ): bool {
        return ProjectMemberAssignment::where(
            'team_project_assignment_id',
            $teamProjectAssignmentId
        )
            ->where('user_id', $userId)
            ->whereNull('released_at')
            ->update(['released_at' => now()->toDateString()]);
    }

    /**
     * Get active members in a project
     */
    public function getProjectMembers(
        int $teamProjectAssignmentId
    ): Collection {
        return ProjectMemberAssignment::with(['user'])
            ->where('team_project_assignment_id', $teamProjectAssignmentId)
            ->whereNull('released_at')
            ->get();
    }
}
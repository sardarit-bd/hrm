<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamProjectAssignment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'team_id',
        'project_id',
        'assigned_by',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'date',
    ];

    // =================== Relationships ===================

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function projectMemberAssignments()
    {
        return $this->hasMany(ProjectMemberAssignment::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_member_assignments')
            ->withPivot('assigned_by', 'assigned_at', 'released_at')
            ->wherePivotNull('released_at');
    }

    public function allMembers()
    {
        return $this->belongsToMany(User::class, 'project_member_assignments')
            ->withPivot('assigned_by', 'assigned_at', 'released_at');
    }

    // =================== Scopes ===================

    public function scopeByTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}
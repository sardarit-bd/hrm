<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'leader_id',
        'created_by',
    ];

    // =================== Relationships ===================

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot('joined_at', 'left_at')
            ->wherePivotNull('left_at');
    }

    public function allMembers()
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot('joined_at', 'left_at');
    }

    public function teamProjectAssignments()
    {
        return $this->hasMany(TeamProjectAssignment::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'team_project_assignments')
            ->withPivot('assigned_by', 'assigned_at');
    }

    // =================== Scopes ===================

    public function scopeWithLeader($query)
    {
        return $query->whereNotNull('leader_id');
    }

    public function scopeWithoutLeader($query)
    {
        return $query->whereNull('leader_id');
    }

    // =================== Helpers ===================

    public function hasLeader(): bool
    {
        return !is_null($this->leader_id);
    }

    public function isLeader(User $user): bool
    {
        return $this->leader_id === $user->id;
    }
}
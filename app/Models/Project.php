<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'name',
        'client_name',
        'description',
        'project_manager_id',
        'type',
        'total_budget',
        'currency',
        'exchange_rate_snapshot',
        'start_date',
        'deadline',
        'delivered_date',
        'status',
    ];

    protected $casts = [
        'total_budget'           => 'decimal:2',
        'exchange_rate_snapshot' => 'decimal:4',
        'start_date'             => 'date',
        'deadline'               => 'date',
        'delivered_date'         => 'date',
    ];

    // =================== Relationships ===================
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function teamProjectAssignments()
    {
        return $this->hasMany(TeamProjectAssignment::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_project_assignments')
            ->withPivot('assigned_by', 'assigned_at')
            ->withTimestamps();
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function hourLogs()
    {
        return $this->hasMany(HourLog::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function projectMemberAssignments()
    {
        return $this->hasManyThrough(
            ProjectMemberAssignment::class,
            TeamProjectAssignment::class
        );
    }

    // =================== Scopes ===================

    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeSingle($query)
    {
        return $query->where('type', 'single');
    }

    public function scopeMilestone($query)
    {
        return $query->where('type', 'milestone');
    }

    public function scopeHourly($query)
    {
        return $query->where('type', 'hourly');
    }

    public function scopeByManager($query, $managerId)
    {
        return $query->where('project_manager_id', $managerId);
    }

    // =================== Helpers ===================

    public function isOngoing(): bool
    {
        return $this->status === 'ongoing';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isSingle(): bool
    {
        return $this->type === 'single';
    }

    public function isMilestone(): bool
    {
        return $this->type === 'milestone';
    }

    public function isHourly(): bool
    {
        return $this->type === 'hourly';
    }

    public function isOverdue(): bool
    {
        return $this->isOngoing() && $this->deadline->isPast();
    }
}
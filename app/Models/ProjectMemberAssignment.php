<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMemberAssignment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'team_project_assignment_id',
        'user_id',
        'assigned_by',
        'assigned_at',
        'released_at',
    ];

    protected $casts = [
        'assigned_at' => 'date',
        'released_at' => 'date',
    ];

    // =================== Relationships ===================

    public function teamProjectAssignment()
    {
        return $this->belongsTo(TeamProjectAssignment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function project()
    {
        return $this->hasOneThrough(
            Project::class,
            TeamProjectAssignment::class,
            'id',
            'id',
            'team_project_assignment_id',
            'project_id'
        );
    }

    public function team()
    {
        return $this->hasOneThrough(
            Team::class,
            TeamProjectAssignment::class,
            'id',
            'id',
            'team_project_assignment_id',
            'team_id'
        );
    }

    // =================== Scopes ===================

    public function scopeActive($query)
    {
        return $query->whereNull('released_at');
    }

    public function scopeReleased($query)
    {
        return $query->whereNotNull('released_at');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // =================== Helpers ===================

    public function isActive(): bool
    {
        return is_null($this->released_at);
    }

    public function release(): bool
    {
        return $this->update(['released_at' => now()->toDateString()]);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'due_date',
        'completion_date',
        'milestone_value',
        'currency',
        'status',
    ];

    protected $casts = [
        'due_date'        => 'date',
        'completion_date' => 'date',
        'milestone_value' => 'decimal:2',
    ];

    // =================== Relationships ===================

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // =================== Scopes ===================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeMissed($query)
    {
        return $query->where('status', 'missed');
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->where('due_date', '<', now()->toDateString());
    }

    // =================== Helpers ===================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isMissed(): bool
    {
        return $this->status === 'missed';
    }

    public function isOverdue(): bool
    {
        return $this->isPending() && $this->due_date->isPast();
    }
}
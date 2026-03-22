<?php

namespace App\Models;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'project_id',
        'from_date',
        'to_date',
        'total_days',
        'reason',
        'status',
    ];

    protected $casts = [
        'from_date'  => 'date',
        'to_date'    => 'date',
        'total_days' => 'integer',
    ];

    // =================== Relationships ===================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function approvals()
    {
        return $this->hasMany(LeaveApproval::class);
    }

    public function pmApproval()
    {
        return $this->hasOne(LeaveApproval::class)
            ->where('approver_role', 'project_manager');
    }

    public function gmApproval()
    {
        return $this->hasOne(LeaveApproval::class)
            ->where('approver_role', 'general_manager');
    }

    // =================== Scopes ===================

    public function scopePendingPm($query)
    {
        return $query->where('status', 'pending_pm');
    }

    public function scopePendingGm($query)
    {
        return $query->where('status', 'pending_gm');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    // =================== Helpers ===================

    public function isPending(): bool
    {
        return in_array($this->status, ['pending_pm','pending_gm']);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
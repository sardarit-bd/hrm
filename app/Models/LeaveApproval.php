<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApproval extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'leave_request_id',
        'approver_id',
        'approver_role',
        'action',
        'remarks',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    // =================== Relationships ===================

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // =================== Scopes ===================

    public function scopeApproved($query)
    {
        return $query->where('action', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('action', 'rejected');
    }

    public function scopeByPm($query)
    {
        return $query->where('approver_role', 'project_manager');
    }

    public function scopeByGm($query)
    {
        return $query->where('approver_role', 'general_manager');
    }

    // =================== Helpers ===================

    public function isApproved(): bool
    {
        return $this->action === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->action === 'rejected';
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HourLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'approved_by',
        'log_date',
        'hours_logged',
        'description',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'log_date'     => 'date',
        'hours_logged' => 'decimal:2',
        'approved_at'  => 'datetime',
    ];

    // =================== Relationships ===================

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // =================== Scopes ===================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('log_date', $year)
            ->whereMonth('log_date', $month);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('log_date', [$from, $to]);
    }

    // =================== Helpers ===================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function approve(User $approver): bool
    {
        return $this->update([
            'status'      => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    public function reject(User $approver): bool
    {
        return $this->update([
            'status'      => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }
}
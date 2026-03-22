<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'roster_assignment_id',
        'policy_id',
        'date',
        'check_in',
        'check_out',
        'expected_check_in',
        'expected_check_out',
        'working_hours',
        'late_minutes',
        'overtime_minutes',
        'is_within_grace_period',
        'status',
    ];

    protected $casts = [
        'date'                   => 'date',
        'check_in'               => 'datetime',
        'check_out'              => 'datetime',
        'working_hours'          => 'decimal:2',
        'late_minutes'           => 'integer',
        'overtime_minutes'       => 'integer',
        'is_within_grace_period' => 'boolean',
    ];

    // =================== Relationships ===================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rosterAssignment()
    {
        return $this->belongsTo(RosterAssignment::class);
    }

    public function policy()
    {
        return $this->belongsTo(AttendancePolicy::class, 'policy_id');
    }

    // =================== Scopes ===================

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeOnLeave($query)
    {
        return $query->where('status', 'on_leave');
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // =================== Helpers ===================

    public function isWeekend(): bool
    {
        return $this->status === 'weekend';
    }

    public function isHoliday(): bool
    {
        return $this->status === 'holiday';
    }

    public function isWorkingDay(): bool
    {
        return !in_array($this->status, ['weekend', 'holiday']);
    }
}
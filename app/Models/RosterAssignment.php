<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id',
        'weekend_days',
        'effective_from',
        'effective_to',
        'assigned_by',
    ];

    protected $casts = [
        'weekend_days'   => 'array',
        'effective_from' => 'date',
        'effective_to'   => 'date',
    ];

    // =================== Relationships ===================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    // =================== Scopes ===================

    public function scopeActive($query)
    {
        return $query->whereNull('effective_to');
    }

    public function scopeActiveAt($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            });
    }

    // =================== Helpers ===================

    public function isWeekend(string $day): bool
    {
        return in_array(strtolower($day), $this->weekend_days ?? []);
    }
}
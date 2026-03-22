<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendancePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grace_period_minutes',
        'late_count_threshold',
        'late_threshold_deduction_days',
        'absent_deduction_per_day',
        'half_day_threshold_hours',
        'effective_from',
        'effective_to',
        'created_by',
    ];

    protected $casts = [
        'grace_period_minutes'          => 'integer',
        'late_count_threshold'          => 'integer',
        'late_threshold_deduction_days' => 'decimal:2',
        'absent_deduction_per_day'      => 'decimal:2',
        'half_day_threshold_hours'      => 'decimal:2',
        'effective_from'                => 'date',
        'effective_to'                  => 'date',
    ];

    // =================== Relationships ===================

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function employeePolicyAssignments()
    {
        return $this->hasMany(EmployeePolicyAssignment::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'policy_id');
    }

    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class);
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
}
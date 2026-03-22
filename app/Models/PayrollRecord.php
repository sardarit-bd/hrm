<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_policy_id',
        'payroll_month',
        'basic_salary',
        'total_working_days',
        'days_present',
        'days_absent',
        'late_count',
        'late_carry_forward_in',
        'late_carry_forward_out',
        'late_deduction_days',
        'late_deduction_amount',
        'absent_deduction_amount',
        'grace_period_used',
        'gross_salary',
        'net_salary',
        'payroll_status',
        'approved_by',
        'paid_at',
        'remarks',
    ];

    protected $casts = [
        'payroll_month'          => 'date',
        'basic_salary'           => 'decimal:2',
        'late_deduction_days'    => 'decimal:2',
        'late_deduction_amount'  => 'decimal:2',
        'absent_deduction_amount'=> 'decimal:2',
        'gross_salary'           => 'decimal:2',
        'net_salary'             => 'decimal:2',
        'total_working_days'     => 'integer',
        'days_present'           => 'integer',
        'days_absent'            => 'integer',
        'late_count'             => 'integer',
        'late_carry_forward_in'  => 'integer',
        'late_carry_forward_out' => 'integer',
        'grace_period_used'      => 'integer',
        'paid_at'                => 'datetime',
    ];

    // =================== Relationships ===================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendancePolicy()
    {
        return $this->belongsTo(AttendancePolicy::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lateDeductionLogs()
    {
        return $this->hasMany(LateDeductionLog::class);
    }

    // =================== Scopes ===================

    public function scopeDraft($query)
    {
        return $query->where('payroll_status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('payroll_status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('payroll_status', 'paid');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('payroll_month', $year)
            ->whereMonth('payroll_month', $month);
    }

    public function scopeByQuarter($query, $year, $quarter)
    {
        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth   = $startMonth + 2;

        return $query->whereYear('payroll_month', $year)
            ->whereMonth('payroll_month', '>=', $startMonth)
            ->whereMonth('payroll_month', '<=', $endMonth);
    }

    // =================== Helpers ===================

    public function isDraft(): bool
    {
        return $this->payroll_status === 'draft';
    }

    public function isApproved(): bool
    {
        return $this->payroll_status === 'approved';
    }

    public function isPaid(): bool
    {
        return $this->payroll_status === 'paid';
    }

    public function approve(User $approver): bool
    {
        return $this->update([
            'payroll_status' => 'approved',
            'approved_by'    => $approver->id,
        ]);
    }

    public function markAsPaid(): bool
    {
        return $this->update([
            'payroll_status' => 'paid',
            'paid_at'        => now(),
        ]);
    }

    public function totalDeductions(): float
    {
        return $this->late_deduction_amount + $this->absent_deduction_amount;
    }
}
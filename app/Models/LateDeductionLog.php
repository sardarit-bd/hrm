<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LateDeductionLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'payroll_record_id',
        'deduction_type',
        'reference_date',
        'deduction_amount',
        'note',
    ];

    protected $casts = [
        'reference_date'   => 'date',
        'deduction_amount' => 'decimal:2',
    ];

    // =================== Relationships ===================

    public function payrollRecord()
    {
        return $this->belongsTo(PayrollRecord::class);
    }

    // =================== Scopes ===================

    public function scopeLate($query)
    {
        return $query->where('deduction_type', 'late');
    }

    public function scopeAbsent($query)
    {
        return $query->where('deduction_type', 'absent');
    }

    public function scopeHalfDay($query)
    {
        return $query->where('deduction_type', 'half_day');
    }

    public function scopeByPayroll($query, $payrollRecordId)
    {
        return $query->where('payroll_record_id', $payrollRecordId);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('reference_date', [$from, $to]);
    }

    // =================== Helpers ===================

    public function isLate(): bool
    {
        return $this->deduction_type === 'late';
    }

    public function isAbsent(): bool
    {
        return $this->deduction_type === 'absent';
    }

    public function isHalfDay(): bool
    {
        return $this->deduction_type === 'half_day';
    }
}
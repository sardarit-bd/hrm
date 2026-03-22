<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'user'                    => new UserResource($this->whenLoaded('user')),
            'attendance_policy'       => new AttendancePolicyResource(
                $this->whenLoaded('attendancePolicy')
            ),
            'payroll_month'           => $this->payroll_month?->format('Y-m'),
            'basic_salary'            => $this->basic_salary,
            'total_working_days'      => $this->total_working_days,
            'days_present'            => $this->days_present,
            'days_absent'             => $this->days_absent,
            'late_count'              => $this->late_count,
            'late_carry_forward_in'   => $this->late_carry_forward_in,
            'late_carry_forward_out'  => $this->late_carry_forward_out,
            'late_deduction_days'     => $this->late_deduction_days,
            'late_deduction_amount'   => $this->late_deduction_amount,
            'absent_deduction_amount' => $this->absent_deduction_amount,
            'total_deductions'        => $this->totalDeductions(),
            'grace_period_used'       => $this->grace_period_used,
            'gross_salary'            => $this->gross_salary,
            'net_salary'              => $this->net_salary,
            'payroll_status'          => $this->payroll_status,
            'approved_by'             => new UserResource(
                $this->whenLoaded('approvedBy')
            ),
            'paid_at'                 => $this->paid_at?->format('Y-m-d H:i:s'),
            'remarks'                 => $this->remarks,
            'deduction_logs'          => LateDeductionLogResource::collection(
                $this->whenLoaded('lateDeductionLogs')
            ),
            'created_at'              => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'              => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
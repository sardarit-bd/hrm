<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class StorePolicyRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                          => ['required', 'string', 'max:100', 'unique:attendance_policies,name'],
            'grace_period_minutes'          => ['required', 'integer', 'min:0', 'max:60'],
            'late_count_threshold'          => ['required', 'integer', 'min:1'],
            'late_threshold_deduction_days' => ['required', 'numeric', 'min:0'],
            'absent_deduction_per_day'      => ['required', 'numeric', 'min:0'],
            'half_day_threshold_hours'      => ['required', 'numeric', 'min:1', 'max:12'],
            'effective_from'                => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                          => 'Policy name is required',
            'name.unique'                            => 'Policy name already exists',
            'grace_period_minutes.required'          => 'Grace period is required',
            'grace_period_minutes.min'               => 'Grace period cannot be negative',
            'grace_period_minutes.max'               => 'Grace period cannot exceed 60 minutes',
            'late_count_threshold.required'          => 'Late count threshold is required',
            'late_count_threshold.min'               => 'Late count threshold must be at least 1',
            'late_threshold_deduction_days.required' => 'Late threshold deduction days is required',
            'absent_deduction_per_day.required'      => 'Absent deduction per day is required',
            'half_day_threshold_hours.required'      => 'Half day threshold hours is required',
            'half_day_threshold_hours.min'           => 'Half day threshold must be at least 1 hour',
            'half_day_threshold_hours.max'           => 'Half day threshold cannot exceed 12 hours',
            'effective_from.required'                => 'Effective from date is required',
            'effective_from.date'                    => 'Effective from must be a valid date',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
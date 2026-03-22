<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class UpdatePolicyRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $policyId = $this->route('policy');

        return [
            'name'                          => ['sometimes', 'string', 'max:100', "unique:attendance_policies,name,{$policyId}"],
            'grace_period_minutes'          => ['sometimes', 'integer', 'min:0', 'max:60'],
            'late_count_threshold'          => ['sometimes', 'integer', 'min:1'],
            'late_threshold_deduction_days' => ['sometimes', 'numeric', 'min:0'],
            'absent_deduction_per_day'      => ['sometimes', 'numeric', 'min:0'],
            'half_day_threshold_hours'      => ['sometimes', 'numeric', 'min:1', 'max:12'],
            'effective_from'                => ['sometimes', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'                  => 'Policy name already exists',
            'grace_period_minutes.min'     => 'Grace period cannot be negative',
            'grace_period_minutes.max'     => 'Grace period cannot exceed 60 minutes',
            'late_count_threshold.min'     => 'Late count threshold must be at least 1',
            'half_day_threshold_hours.min' => 'Half day threshold must be at least 1 hour',
            'half_day_threshold_hours.max' => 'Half day threshold cannot exceed 12 hours',
            'effective_from.date'          => 'Effective from must be a valid date',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
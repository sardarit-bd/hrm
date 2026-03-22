<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class AssignPolicyRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'               => ['required', 'integer', 'exists:users,id'],
            'attendance_policy_id'  => ['required', 'integer', 'exists:attendance_policies,id'],
            'effective_from'        => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'              => 'User is required',
            'user_id.exists'                => 'User not found',
            'attendance_policy_id.required' => 'Attendance policy is required',
            'attendance_policy_id.exists'   => 'Attendance policy not found',
            'effective_from.required'       => 'Effective from date is required',
            'effective_from.date'           => 'Effective from must be a valid date',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
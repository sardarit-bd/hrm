<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class StoreLeaveRequestRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'project_id'    => ['nullable', 'integer', 'exists:projects,id'],
            'from_date'     => ['required', 'date', 'after_or_equal:today'],
            'to_date'       => ['required', 'date', 'after_or_equal:from_date'],
            'reason'        => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'leave_type_id.required'   => 'Leave type is required',
            'leave_type_id.exists'     => 'Leave type not found',
            'project_id.exists'        => 'Project not found',
            'from_date.required'       => 'From date is required',
            'from_date.after_or_equal' => 'From date cannot be in the past',
            'to_date.required'         => 'To date is required',
            'to_date.after_or_equal'   => 'To date must be after or equal to from date',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class UpdateLeaveTypeRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $leaveTypeId = $this->route('leaveType');

        return [
            'name'              => ['sometimes', 'string', 'max:100', "unique:leave_types,name,{$leaveTypeId}"],
            'max_days_per_year' => ['sometimes', 'integer', 'min:1', 'max:365'],
            'is_paid'           => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'           => 'Leave type name already exists',
            'max_days_per_year.min' => 'Maximum days must be at least 1',
            'max_days_per_year.max' => 'Maximum days cannot exceed 365',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
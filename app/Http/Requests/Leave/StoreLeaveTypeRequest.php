<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class StoreLeaveTypeRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:100', 'unique:leave_types,name'],
            'max_days_per_year'  => ['required', 'integer', 'min:1', 'max:365'],
            'is_paid'            => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'              => 'Leave type name is required',
            'name.unique'                => 'Leave type name already exists',
            'max_days_per_year.required' => 'Maximum days per year is required',
            'max_days_per_year.min'      => 'Maximum days must be at least 1',
            'max_days_per_year.max'      => 'Maximum days cannot exceed 365',
            'is_paid.required'           => 'Please specify if leave is paid or unpaid',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
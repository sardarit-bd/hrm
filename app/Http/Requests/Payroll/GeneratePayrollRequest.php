<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class GeneratePayrollRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'       => ['required', 'integer', 'exists:users,id'],
            'payroll_month' => ['required', 'date_format:Y-m'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'       => 'User is required',
            'user_id.exists'         => 'User not found',
            'payroll_month.required' => 'Payroll month is required',
            'payroll_month.date_format' => 'Payroll month must be in YYYY-MM format',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
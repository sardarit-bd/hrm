<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class UpdatePayrollRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'remarks'        => ['nullable', 'string', 'max:500'],
            'payroll_status' => ['sometimes', 'in:draft,approved,paid'],
        ];
    }

    public function messages(): array
    {
        return [
            'payroll_status.in' => 'Status must be draft, approved or paid',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
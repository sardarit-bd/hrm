<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class StoreHourLogRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'   => ['required', 'integer', 'exists:projects,id'],
            'log_date'     => ['required', 'date', 'before_or_equal:today'],
            'hours_logged' => ['required', 'numeric', 'min:0.5', 'max:24'],
            'description'  => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required'    => 'Project is required',
            'project_id.exists'      => 'Project not found',
            'log_date.required'      => 'Log date is required',
            'log_date.date'          => 'Log date must be a valid date',
            'log_date.before_or_equal' => 'Log date cannot be in the future',
            'hours_logged.required'  => 'Hours logged is required',
            'hours_logged.min'       => 'Minimum 0.5 hours must be logged',
            'hours_logged.max'       => 'Cannot log more than 24 hours per entry',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
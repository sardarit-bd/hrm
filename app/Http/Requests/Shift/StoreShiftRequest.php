<?php

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class StoreShiftRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:100', 'unique:shifts,name'],
            'start_time'    => ['required', 'date_format:H:i'],
            'end_time'      => ['required', 'date_format:H:i'],
            'cross_midnight'=> ['sometimes', 'boolean'],
            'working_hours' => ['required', 'numeric', 'min:1', 'max:24'],
            'is_fixed'      => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'Shift name is required',
            'name.unique'            => 'Shift name already exists',
            'start_time.required'    => 'Start time is required',
            'start_time.date_format' => 'Start time must be in HH:MM format',
            'end_time.required'      => 'End time is required',
            'end_time.date_format'   => 'End time must be in HH:MM format',
            'working_hours.required' => 'Working hours is required',
            'working_hours.min'      => 'Working hours must be at least 1',
            'working_hours.max'      => 'Working hours cannot exceed 24',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
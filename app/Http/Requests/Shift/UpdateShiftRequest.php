<?php

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class UpdateShiftRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $shiftId = $this->route('shift');

        return [
            'name'          => ['sometimes', 'string', 'max:100', "unique:shifts,name,{$shiftId}"],
            'start_time'    => ['sometimes', 'date_format:H:i'],
            'end_time'      => ['sometimes', 'date_format:H:i'],
            'cross_midnight'=> ['sometimes', 'boolean'],
            'working_hours' => ['sometimes', 'numeric', 'min:1', 'max:24'],
            'is_fixed'      => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'            => 'Shift name already exists',
            'start_time.date_format' => 'Start time must be in HH:MM format',
            'end_time.date_format'   => 'End time must be in HH:MM format',
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
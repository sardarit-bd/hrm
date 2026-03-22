<?php

namespace App\Http\Requests\Roster;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class UpdateRosterRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift_id'       => ['sometimes', 'integer', 'exists:shifts,id'],
            'weekend_days'   => ['sometimes', 'array', 'min:1'],
            'weekend_days.*' => ['required_with:weekend_days', 'string', 'in:saturday,sunday,monday,tuesday,wednesday,thursday,friday'],
            'effective_from' => ['sometimes', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'shift_id.exists'       => 'Shift not found',
            'weekend_days.array'    => 'Weekend days must be an array',
            'weekend_days.min'      => 'At least one weekend day is required',
            'weekend_days.*.in'     => 'Invalid day provided in weekend days',
            'effective_from.date'   => 'Effective from must be a valid date',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
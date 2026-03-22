<?php

namespace App\Http\Requests\Roster;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class StoreRosterRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'        => ['required', 'integer', 'exists:users,id'],
            'shift_id'       => ['required', 'integer', 'exists:shifts,id'],
            'weekend_days'   => ['required', 'array', 'min:1'],
            'weekend_days.*' => ['required', 'string', 'in:saturday,sunday,monday,tuesday,wednesday,thursday,friday'],
            'effective_from' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'        => 'User is required',
            'user_id.exists'          => 'User not found',
            'shift_id.required'       => 'Shift is required',
            'shift_id.exists'         => 'Shift not found',
            'weekend_days.required'   => 'Weekend days are required',
            'weekend_days.array'      => 'Weekend days must be an array',
            'weekend_days.min'        => 'At least one weekend day is required',
            'weekend_days.*.in'       => 'Invalid day provided in weekend days',
            'effective_from.required' => 'Effective from date is required',
            'effective_from.date'     => 'Effective from must be a valid date',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
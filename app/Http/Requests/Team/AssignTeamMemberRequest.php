<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class AssignTeamMemberRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'   => ['required', 'integer', 'exists:users,id'],
            'joined_at' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'  => 'User is required',
            'user_id.exists'    => 'User not found',
            'joined_at.required'=> 'Joined date is required',
            'joined_at.date'    => 'Joined date must be a valid date',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
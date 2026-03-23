<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class UpdateTeamRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teamId = $this->route('team');

        return [
            'name'      => ['sometimes', 'string', 'max:255', "unique:teams,name,{$teamId}"],
            'leader_id' => ['nullable', 'integer', 'exists:users,id'],
            'name'          => ['sometimes', 'string', 'max:255', "unique:teams,name,{$teamId}"],
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'leader_id'     => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'      => 'Team name already exists',
            'leader_id.exists' => 'Team leader not found',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
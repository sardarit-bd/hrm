<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class AssignProjectMemberRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'                    => ['required', 'integer', 'exists:users,id'],
            'team_project_assignment_id' => ['required', 'integer', 'exists:team_project_assignments,id'],
            'assigned_at'                => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'                    => 'User is required',
            'user_id.exists'                      => 'User not found',
            'team_project_assignment_id.required' => 'Team project assignment is required',
            'team_project_assignment_id.exists'   => 'Team project assignment not found',
            'assigned_at.required'                => 'Assigned date is required',
            'assigned_at.date'                    => 'Assigned date must be a valid date',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
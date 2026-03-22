<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class AssignTeamProjectRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'  => ['required', 'integer', 'exists:projects,id'],
            'assigned_at' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required'  => 'Project is required',
            'project_id.exists'    => 'Project not found',
            'assigned_at.required' => 'Assigned date is required',
            'assigned_at.date'     => 'Assigned date must be a valid date',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
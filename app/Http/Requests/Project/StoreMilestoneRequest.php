<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class StoreMilestoneRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'      => ['required', 'integer', 'exists:projects,id'],
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'due_date'        => ['required', 'date'],
            'milestone_value' => ['required', 'numeric', 'min:0'],
            'currency'        => ['required', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required'      => 'Project is required',
            'project_id.exists'        => 'Project not found',
            'title.required'           => 'Milestone title is required',
            'due_date.required'        => 'Due date is required',
            'due_date.date'            => 'Due date must be a valid date',
            'milestone_value.required' => 'Milestone value is required',
            'milestone_value.min'      => 'Milestone value cannot be negative',
            'currency.required'        => 'Currency is required',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class UpdateMilestoneRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'           => ['sometimes', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'due_date'        => ['sometimes', 'date'],
            'completion_date' => ['nullable', 'date'],
            'milestone_value' => ['sometimes', 'numeric', 'min:0'],
            'currency'        => ['sometimes', 'string', 'max:10'],
            'status'          => ['sometimes', 'in:pending,completed,missed'],
        ];
    }

    public function messages(): array
    {
        return [
            'due_date.date'        => 'Due date must be a valid date',
            'completion_date.date' => 'Completion date must be a valid date',
            'milestone_value.min'  => 'Milestone value cannot be negative',
            'status.in'            => 'Status must be pending, completed or missed',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
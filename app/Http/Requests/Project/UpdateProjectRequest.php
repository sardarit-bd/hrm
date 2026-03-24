<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class UpdateProjectRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel_id'              => ['sometimes', 'integer', 'exists:channels,id'],
            'name'                    => ['sometimes', 'string', 'max:255'],
            'client_name'             => ['sometimes', 'string', 'max:255'],
            'description'             => ['nullable', 'string'],
            'project_manager_id'      => ['sometimes', 'integer', 'exists:users,id'],
            'type'                    => ['sometimes', 'in:single,milestone,hourly'],
            'total_budget'            => ['sometimes', 'numeric', 'min:0'],
            'currency'                => ['sometimes', 'string', 'max:10'],
            'exchange_rate_snapshot'  => ['sometimes', 'numeric', 'min:0'],
            'start_date'              => ['sometimes', 'date'],
            'deadline'                => ['sometimes', 'date', 'after_or_equal:start_date'],
            'delivered_date'          => ['nullable', 'date'],
            'status'                  => ['sometimes', 'in:ongoing,delivered,cancelled'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_manager_id.exists' => 'Project manager not found',
            'type.in'                   => 'Project type must be single, milestone or hourly',
            'total_budget.min'          => 'Total budget cannot be negative',
            'deadline.after'            => 'Deadline must be after start date',
            'status.in'                 => 'Status must be ongoing, delivered or cancelled',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
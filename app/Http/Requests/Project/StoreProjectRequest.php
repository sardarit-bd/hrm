<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class StoreProjectRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                   => ['required', 'string', 'max:255'],
            'client_name'            => ['required', 'string', 'max:255'],
            'description'            => ['nullable', 'string'],
            'project_manager_id'     => ['required', 'integer', 'exists:users,id'],
            'type'                   => ['required', 'in:single,milestone,hourly'],
            'total_budget'           => ['required', 'numeric', 'min:0'],
            'currency'               => ['required', 'string', 'max:10'],
            'exchange_rate_snapshot' => ['required', 'numeric', 'min:0'],
            'start_date'             => ['required', 'date'],
            'deadline'               => ['required', 'date', 'after:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                   => 'Project name is required',
            'client_name.required'            => 'Client name is required',
            'project_manager_id.required'     => 'Project manager is required',
            'project_manager_id.exists'       => 'Project manager not found',
            'type.required'                   => 'Project type is required',
            'type.in'                         => 'Project type must be single, milestone or hourly',
            'total_budget.required'           => 'Total budget is required',
            'total_budget.min'                => 'Total budget cannot be negative',
            'currency.required'               => 'Currency is required',
            'exchange_rate_snapshot.required' => 'Exchange rate is required',
            'start_date.required'             => 'Start date is required',
            'deadline.required'               => 'Deadline is required',
            'deadline.after'                  => 'Deadline must be after start date',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
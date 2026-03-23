<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class UpdateDepartmentRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department');

        return [
            'name'        => ['sometimes', 'string', 'max:100', "unique:departments,name,{$departmentId}"],
            'description' => ['nullable', 'string', 'max:500'],
            'manager_id'  => ['nullable', 'integer', 'exists:users,id'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'       => 'Department name already exists',
            'manager_id.exists' => 'Manager not found',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
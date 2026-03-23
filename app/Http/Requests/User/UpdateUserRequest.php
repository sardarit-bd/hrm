<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;
use App\Traits\ApiResponseTrait;

class UpdateUserRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'employee_code' => ['sometimes', 'string', "unique:users,employee_code,{$userId}"],
            'full_name'     => ['sometimes', 'string', 'max:255'],
            'email'         => ['sometimes', 'email', "unique:users,email,{$userId}"],
            'password'      => ['sometimes', Password::min(8)
                                    ->mixedCase()
                                    ->numbers()
                                    ->symbols()
                                ],
            'role'          => ['sometimes', 'in:super_admin,general_manager,project_manager,team_leader,employee'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'designation'   => ['nullable', 'string', 'max:100'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'joining_date'  => ['sometimes', 'date', 'before_or_equal:today'],
            'status'        => ['sometimes', 'in:active,inactive,terminated'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_code.unique' => 'Employee code already exists',
            'email.email'          => 'Email must be a valid email address',
            'email.unique'         => 'Email already exists',
            'role.in'              => 'Invalid role provided',
            'joining_date.date'    => 'Joining date must be a valid date',
            'joining_date.before_or_equal' => 'Joining date cannot be in the future',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
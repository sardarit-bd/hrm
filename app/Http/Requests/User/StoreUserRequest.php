<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;
use App\Traits\ApiResponseTrait;

class StoreUserRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_code' => ['required', 'string', 'unique:users,employee_code'],
            'full_name'     => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'password'      => ['required', Password::min(8)
                                    ->mixedCase()
                                    ->numbers()
                                    ->symbols()
                                ],
            'role'          => ['required', 'in:super_admin,general_manager,project_manager,team_leader,employee'],
            'department'    => ['nullable', 'string', 'max:100'],
            'designation'   => ['nullable', 'string', 'max:100'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'joining_date'  => ['required', 'date', 'before_or_equal:today'],
            'status'        => ['sometimes', 'in:active,inactive,terminated'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_code.required' => 'Employee code is required',
            'employee_code.unique'   => 'Employee code already exists',
            'full_name.required'     => 'Full name is required',
            'email.required'         => 'Email is required',
            'email.email'            => 'Email must be a valid email address',
            'email.unique'           => 'Email already exists',
            'password.required'      => 'Password is required',
            'role.required'          => 'Role is required',
            'role.in'                => 'Invalid role provided',
            'joining_date.required'  => 'Joining date is required',
            'joining_date.date'      => 'Joining date must be a valid date',
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
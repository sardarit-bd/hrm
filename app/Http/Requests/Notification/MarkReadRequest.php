<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class MarkReadRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notification_ids'   => ['required', 'array', 'min:1'],
            'notification_ids.*' => ['required', 'integer', 'exists:notifications,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'notification_ids.required'   => 'Notification IDs are required',
            'notification_ids.array'      => 'Notification IDs must be an array',
            'notification_ids.min'        => 'At least one notification ID is required',
            'notification_ids.*.integer'  => 'Notification ID must be an integer',
            'notification_ids.*.exists'   => 'One or more notifications not found',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
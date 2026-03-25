<?php

namespace App\Http\Requests\Notification;

use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCustomNotificationRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_ids'   => ['required', 'array', 'min:1'],
            'recipient_ids.*' => ['required', 'integer', 'exists:users,id'],
            'title'           => ['required', 'string', 'max:255'],
            'message'         => ['required', 'string', 'max:2000'],
            'type'            => ['required', 'string', 'max:100'],
            'context'         => ['sometimes', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_ids.required'   => 'At least one recipient is required',
            'recipient_ids.*.exists'   => 'One or more recipients were not found',
            'title.required'           => 'Title is required',
            'message.required'         => 'Message is required',
            'type.required'            => 'Type is required',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}

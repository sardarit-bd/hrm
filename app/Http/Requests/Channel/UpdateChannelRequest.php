<?php

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class UpdateChannelRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $channelId = $this->route('channel');

        return [
            'name'      => ['sometimes', 'string', 'max:100', "unique:channels,name,{$channelId}"],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Channel name already exists',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
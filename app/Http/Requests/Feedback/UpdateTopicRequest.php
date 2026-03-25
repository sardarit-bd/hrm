<?php

namespace App\Http\Requests\Feedback;

use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTopicRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $topicId = $this->route('id');

        return [
            'name'        => ['sometimes', 'string', 'max:100', "unique:topics,name,{$topicId}"],
            'slug'        => ['sometimes', 'string', 'max:120', 'alpha_dash', "unique:topics,slug,{$topicId}"],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Topic name already exists',
            'slug.unique' => 'Topic slug already exists',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}

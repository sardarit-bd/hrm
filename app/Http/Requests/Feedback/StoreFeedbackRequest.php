<?php

namespace App\Http\Requests\Feedback;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class StoreFeedbackRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category'  => ['required', 'in:work_environment,management,process,compensation,other'],
            'message'   => ['required', 'string', 'min:10', 'max:1000'],
            'sentiment' => ['required', 'in:positive,neutral,negative'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required'  => 'Category is required',
            'category.in'        => 'Invalid category provided',
            'message.required'   => 'Message is required',
            'message.min'        => 'Message must be at least 10 characters',
            'message.max'        => 'Message cannot exceed 1000 characters',
            'sentiment.required' => 'Sentiment is required',
            'sentiment.in'       => 'Sentiment must be positive, neutral or negative',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
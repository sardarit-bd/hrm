<?php

namespace App\Http\Requests\Feedback;

use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

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
            'topic_id'   => [
                'required',
                'integer',
                Rule::exists('topics', 'id')->where(fn($query) => $query->where('is_active', true)),
            ],
            'message'    => ['required', 'string', 'min:10', 'max:1000'],
            'sentiment'  => ['required', 'in:positive,neutral,negative'],
        ];
    }

    public function messages(): array
    {
        return [
            'topic_id.required' => 'Topic is required',
            'topic_id.exists'   => 'Selected topic is invalid or inactive',
            'message.required'  => 'Message is required',
            'message.min'       => 'Message must be at least 10 characters',
            'message.max'       => 'Message cannot exceed 1000 characters',
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

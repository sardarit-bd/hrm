<?php

namespace App\Http\Requests\Zkteco;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class ZktecoSyncRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id'              => ['required', 'string'],
            'synced_at'              => ['required', 'date'],
            'punches'                => ['required', 'array', 'min:1'],
            'punches.*.uid'          => ['required', 'integer'],
            'punches.*.id'           => ['required', 'string'],
            'punches.*.punch_time'   => ['required', 'date'],
            'punches.*.type'         => ['required', 'integer', 'in:0,1'],
            'punches.*.state'        => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'device_id.required'            => 'Device ID is required',
            'synced_at.required'            => 'Synced at timestamp is required',
            'punches.required'              => 'Punch data is required',
            'punches.min'                   => 'At least one punch record is required',
            'punches.*.uid.required'        => 'Punch UID is required',
            'punches.*.id.required'         => 'Employee ID is required',
            'punches.*.punch_time.required' => 'Punch time is required',
            'punches.*.type.required'       => 'Punch type is required',
            'punches.*.type.in'             => 'Punch type must be 0 (exit) or 1 (entry)',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            $this->validationErrorResponse($validator->errors())
        );
    }
}
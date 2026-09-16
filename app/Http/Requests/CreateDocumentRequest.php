<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documentType' => 'required|string|max:100',
            'reason' => 'required|string|max:500',
            'urgency' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return {};
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first() ?: 'Erreur de validation',
            'errors' => $validator->errors(),
        ], 422));
    }
}

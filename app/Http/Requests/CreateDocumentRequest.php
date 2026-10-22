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

    protected function prepareForValidation(): void
    {
        if ($this->has('document_type') && !$this->has('documentType')) {
            $this->merge(['documentType' => $this->input('document_type')]);
        }
        if ($this->has('documentType') && !$this->has('document_type')) {
            $this->merge(['document_type' => $this->input('documentType')]);
        }
    }

    public function rules(): array
    {
        return [
            'documentType' => 'required|string|max:100',
            'reason' => 'nullable|string|max:500',
            'urgency' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'documentType.required' => 'Le type de document est requis.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'success' => false,
            'message' => $validator->errors()->first() ?: 'Erreur de validation',
            'errors' => $validator->errors(),
        ], 422));
    }
}

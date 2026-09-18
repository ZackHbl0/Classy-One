<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ParentDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('urgency')) {
            $raw = strtolower((string) $this->urgency);
            $normalized = str_starts_with($raw, 'urg') ? 'urgent' : 'normal';
            $this->merge(['urgency' => $normalized]);
        }
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|integer|exists:student,idStudent',
            'document_type' => 'required|string|max:100',
            'reason' => 'nullable|string|max:500',
            'comments' => 'nullable|string|max:500',
            'urgency' => 'nullable|string|in:normal,urgent,Normal,Urgent,normale,urgente,Normale,Urgente',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Veuillez sélectionner un élève.',
            'student_id.exists' => 'Élève introuvable.',
            'document_type.required' => 'Le type de document est requis.',
            'urgency.in' => "Niveau d'urgence invalide.",
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

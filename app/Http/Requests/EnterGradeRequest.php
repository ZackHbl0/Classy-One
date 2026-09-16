<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EnterGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|integer|exists:student,idStudent',
            'course_id' => 'required|integer|exists:courses,id',
            'classe_id' => 'required|integer|exists:classe,id',
            'note' => 'required|numeric|min:0|max:20',
            'type' => 'required|string|max:50',
            'subject_name' => 'required|string|max:100',
            'exam_date' => 'required|date',
            'comment' => 'nullable|string|max:1000',
            'semester' => 'required|string|in:S1,S2,Semestre 1,Semestre 2',
        ];
    }

    public function messages(): array
    {
        return [
            'note.min' => 'La note ne peut pas être inférieure à 0.',
            'note.max' => 'La note ne peut pas être supérieure à 20.',
            'student_id.exists' => 'L\'étudiant sélectionné n\'existe pas.',
        ];
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

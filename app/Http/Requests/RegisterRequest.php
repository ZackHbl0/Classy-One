<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matricule' => 'required|string|max:50|unique:student,matricule',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'password' => 'required|string|min:6',
            'telephone' => 'nullable|string|max:20',
            'fcmToken' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'matricule.required' => 'Le matricule est obligatoire.',
            'matricule.unique' => 'Ce matricule est déjà utilisé.',
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit comporter au moins 6 caractères.',
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

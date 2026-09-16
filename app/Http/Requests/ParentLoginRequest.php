<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ParentLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|string|max:100',
            'password' => 'required|string|min:4',
            'fcmToken' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L\'e-mail ou le téléphone est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
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

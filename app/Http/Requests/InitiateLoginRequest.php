<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiateLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Tout le monde peut initier une connexion
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numeroTelephone' => [
                'required',
                'string',
                'regex:/^\+221[0-9]{9}$/',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'numeroTelephone.required' => 'Le numéro de téléphone est obligatoire pour l\'authentification.',
            'numeroTelephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'numeroTelephone.regex' => 'Le numéro de téléphone doit être un numéro sénégalais valide commençant par +221 suivi de 9 chiffres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'numeroTelephone' => 'numéro de téléphone',
        ];
    }
}

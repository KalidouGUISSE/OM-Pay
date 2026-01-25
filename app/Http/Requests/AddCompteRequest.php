<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Vérifier que l'utilisateur est authentifié
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
                'unique:comptes,numeroTelephone',
            ],
            'type' => 'required|in:simple,marchand',
            'statut' => 'nullable|in:actif,bloque,ferme',
            'codePing' => 'nullable|string|min:4',
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    public function messages(): array
    {
        return [
            'numeroTelephone.regex' => 'Le numéro de téléphone doit être un numéro sénégalais valide commençant par +221 suivi de 9 chiffres.',
            'numeroTelephone.unique' => 'Ce numéro de téléphone est déjà utilisé pour un compte.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ou vérifier le rôle si nécessaire
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numero_carte_identite' => [
                'required',
                'string',
                'regex:/^\d{13}$/',
                'unique:users,numero_carte_identite',
            ],
            'numeroTelephone' => [
                'required',
                'string',
                'regex:/^\+221[0-9]{9}$/',
                'unique:comptes,numeroTelephone',
            ],
            'type' => 'required|in:simple,marchand',
            'dateCreation' => 'nullable|date',
            'statut' => 'nullable|in:actif,bloque,ferme',
            'codePing' => 'nullable|string|min:4',
            'nom' => 'nullable|string',
            'prenom' => 'nullable|string',
            'email' => 'nullable|email',
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    public function messages(): array
    {
        return [
            'numero_carte_identite.unique' => 'Ce numéro de carte d\'identité est déjà utilisé.',
            'numero_carte_identite.regex' => 'Le numéro de carte d\'identité doit être un numéro sénégalais valide de 13 chiffres.',
            'numeroTelephone.regex' => 'Le numéro de téléphone doit être un numéro sénégalais valide commençant par +221 suivi de 9 chiffres.',
            'numeroTelephone.unique' => 'Ce numéro de téléphone est déjà utilisé pour un compte.',
            'email.unique' => 'Cet email est déjà utilisé.',
        ];
    }
}

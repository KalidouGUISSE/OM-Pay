<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\CompteRepository;
use Illuminate\Validation\Rule;

class CreateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Vérifier que l'utilisateur a le rôle client ou admin
        $user = $this->user();
        return $user && in_array($user->role, ['client', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $compteRepository = app(CompteRepository::class);
        $numeroExpediteur = $this->route('numero'); // Récupérer depuis l'URL

        return [
            'numero du destinataire' => [
                'required',
                'string',
                'regex:/^\+221[0-9]{9}$/',
                function ($attribute, $value, $fail) use ($compteRepository) {
                    // Vérifier que le compte destinataire existe
                    $destinataireCompte = $compteRepository->findByNumeroTelephone($value);
                    if (!$destinataireCompte) {
                        $fail('Le compte destinataire n\'existe pas.');
                    }
                },
                function ($attribute, $value, $fail) use ($numeroExpediteur) {
                    // Vérifier que le destinataire est différent de l'expéditeur
                    if ($numeroExpediteur && $value === $numeroExpediteur) {
                        $fail('Le numéro du destinataire ne peut pas être le même que celui de l\'expéditeur.');
                    }
                },
            ],
            'montant' => [
                'required',
                'numeric',
                'min:0.01',
                'max:1000000', // Limite maximale pour éviter les abus
            ],
            'type_transaction' => [
                'required',
                'string',
                Rule::in(['transfert', 'Transfert d\'argent', 'dépôt', 'retrait']),
            ],
            'date' => 'nullable|date|after:now',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'numero du destinataire.required' => 'Le numéro du destinataire est obligatoire.',
            'numero du destinataire.string' => 'Le numéro du destinataire doit être une chaîne de caractères.',
            'numero du destinataire.regex' => 'Le numéro du destinataire doit être un numéro sénégalais valide commençant par +221 suivi de 9 chiffres.',
            'montant.required' => 'Le montant de la transaction est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre valide.',
            'montant.min' => 'Le montant doit être supérieur à 0.',
            'montant.max' => 'Le montant ne peut pas dépasser 1 000 000 FCFA.',
            'type_transaction.required' => 'Le type de transaction est obligatoire.',
            'type_transaction.string' => 'Le type de transaction doit être une chaîne de caractères.',
            'type_transaction.in' => 'Le type de transaction doit être : transfert, Transfert d\'argent, dépôt ou retrait.',
            'date.date' => 'La date doit être une date valide.',
            'date.after' => 'La date ne peut pas être dans le passé.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'numero du destinataire' => 'numéro du destinataire',
            'montant' => 'montant',
            'type_transaction' => 'type de transaction',
            'date' => 'date',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $numeroExpediteur = $this->route('numero');

        // Mapper les champs du request vers les noms attendus par le modèle
        $this->merge([
            'destinataire' => $this->input('numero du destinataire'),
            'expediteur' => $numeroExpediteur,
        ]);
    }
}

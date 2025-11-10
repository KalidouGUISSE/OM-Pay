<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\CompteRepository;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
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
        $user = $this->user();

        // Récupérer le numéro de téléphone de l'utilisateur connecté depuis son compte
        $userCompte = $compteRepository->findById($user->id); // Supposant que user->id correspond à id_client
        $numeroExpediteur = $userCompte ? $userCompte->numeroTelephone : null;

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
            'montant' => 'required|numeric|min:0.01',
            'type_transaction' => [
                'required',
                'string',
                Rule::in(['transfert', 'Transfert d\'argent']), // Types valides
            ],
            'date' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'numero du destinataire.required' => 'Le numéro du destinataire est obligatoire.',
            'numero du destinataire.regex' => 'Le numéro du destinataire doit être un numéro sénégalais valide commençant par +221 suivi de 9 chiffres.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être supérieur à 0.',
            'type_transaction.required' => 'Le type de transaction est obligatoire.',
            'type_transaction.in' => 'Le type de transaction doit être "transfert" ou "Transfert d\'argent".',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $compteRepository = app(CompteRepository::class);
        $user = $this->user();

        // Pour les clients, récupérer le numéro depuis leur compte
        // Pour les admins, utiliser une valeur par défaut ou depuis le token
        $numeroExpediteur = '+221776458909'; // Valeur par défaut

        if ($user && $user->role === 'client') {
            // Extraire l'ID du compte depuis les abilities du token
            $token = $user->currentAccessToken();
            if ($token) {
                foreach ($token->abilities ?? [] as $ability) {
                    if (str_starts_with($ability, 'compte_id:')) {
                        $compteId = str_replace('compte_id:', '', $ability);
                        $userCompte = $compteRepository->findById($compteId);
                        if ($userCompte) {
                            $numeroExpediteur = $userCompte->numeroTelephone;
                        }
                        break;
                    }
                }
            }
        }

        // Mapper les champs du request vers les noms attendus par le modèle
        $this->merge([
            'destinataire' => $this->input('numero du destinataire'),
            'expediteur' => $numeroExpediteur,
        ]);
    }
}

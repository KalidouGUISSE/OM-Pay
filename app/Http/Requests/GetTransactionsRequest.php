<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetTransactionsRequest extends FormRequest
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
        return [
            'type' => [
                'nullable',
                'string',
                'in:Dépôt,Retrait,Transfert d\'argent,dépôt,retrait,transfert',
            ],
            'date_from' => [
                'nullable',
                'date',
                'before_or_equal:date_to',
            ],
            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
            'direction' => [
                'nullable',
                'string',
                'in:incoming,outgoing,entrant,sortant',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'sort_by' => [
                'nullable',
                'string',
                'in:date,amount,montant,type,reference',
            ],
            'sort_direction' => [
                'nullable',
                'string',
                'in:asc,desc,ASC,DESC',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.string' => 'Le type de filtrage doit être une chaîne de caractères.',
            'type.in' => 'Le type doit être : Dépôt, Retrait, Transfert d\'argent, dépôt, retrait ou transfert.',
            'date_from.date' => 'La date de début doit être une date valide.',
            'date_from.before_or_equal' => 'La date de début ne peut pas être postérieure à la date de fin.',
            'date_to.date' => 'La date de fin doit être une date valide.',
            'date_to.after_or_equal' => 'La date de fin ne peut pas être antérieure à la date de début.',
            'direction.string' => 'La direction doit être une chaîne de caractères.',
            'direction.in' => 'La direction doit être : incoming, outgoing, entrant ou sortant.',
            'per_page.integer' => 'Le nombre d\'éléments par page doit être un entier.',
            'per_page.min' => 'Le nombre d\'éléments par page doit être d\'au moins 1.',
            'per_page.max' => 'Le nombre d\'éléments par page ne peut pas dépasser 100.',
            'sort_by.string' => 'Le champ de tri doit être une chaîne de caractères.',
            'sort_by.in' => 'Le champ de tri doit être : date, amount, montant, type ou reference.',
            'sort_direction.string' => 'La direction de tri doit être une chaîne de caractères.',
            'sort_direction.in' => 'La direction de tri doit être : asc, desc, ASC ou DESC.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'type' => 'type de transaction',
            'date_from' => 'date de début',
            'date_to' => 'date de fin',
            'direction' => 'direction',
            'per_page' => 'éléments par page',
            'sort_by' => 'champ de tri',
            'sort_direction' => 'direction de tri',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normaliser les valeurs
        $this->merge([
            'sort_direction' => strtolower($this->input('sort_direction', 'desc')),
        ]);
    }
}

<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Contracts\Interfaces\CompteRepositoryInterface;

class MerchantTransferValidator implements ValidationRule
{
    protected CompteRepositoryInterface $compteRepository;

    public function __construct()
    {
        $this->compteRepository = app(CompteRepositoryInterface::class);
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Vérifier que le code marchand n'est pas vide
        if (empty($value)) {
            $fail('Le code marchand est requis pour ce type de transfert.');
            return;
        }

        // Vérifier que le code marchand existe et correspond à un compte marchand actif
        $compteMarchand = $this->compteRepository->findByMerchantCode($value);
        if (!$compteMarchand) {
            $fail('Le code marchand fourni ne correspond à aucun compte marchand actif.');
            return;
        }

        // Vérifier que le compte est bien de type marchand
        if ($compteMarchand->type !== 'marchand') {
            $fail('Le code fourni ne correspond pas à un compte marchand.');
            return;
        }

        // Vérifier que le compte marchand est actif
        if ($compteMarchand->statut !== 'actif') {
            $fail('Le compte marchand n\'est pas actif.');
            return;
        }
    }
}

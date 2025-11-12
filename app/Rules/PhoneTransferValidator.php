<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Contracts\Interfaces\CompteRepositoryInterface;

class PhoneTransferValidator implements ValidationRule
{
    protected CompteRepositoryInterface $compteRepository;
    protected string $expediteur;

    public function __construct(string $expediteur = null)
    {
        $this->compteRepository = app(CompteRepositoryInterface::class);
        $this->expediteur = $expediteur;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Vérifier le format du numéro de téléphone
        if (!preg_match('/^\+221[0-9]{9}$/', $value)) {
            $fail('Le numéro de téléphone du destinataire doit être un numéro sénégalais valide commençant par +221 suivi de 9 chiffres.');
            return;
        }

        // Vérifier que le numéro de téléphone n'est pas le même que l'expéditeur
        if ($this->expediteur && $value === $this->expediteur) {
            $fail('Vous ne pouvez pas effectuer un transfert vers votre propre numéro de téléphone.');
            return;
        }

        // Vérifier que le compte destinataire existe
        $compteDestinataire = $this->compteRepository->findByNumeroTelephone($value);
        if (!$compteDestinataire) {
            $fail('Le numéro de téléphone du destinataire ne correspond à aucun compte actif.');
            return;
        }

        // Vérifier que le compte destinataire est actif
        if ($compteDestinataire->statut !== 'actif') {
            $fail('Le compte destinataire n\'est pas actif.');
            return;
        }
    }
}

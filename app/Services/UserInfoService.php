<?php

namespace App\Services;

use App\Models\Compte;
use App\Contracts\Interfaces\TransactionRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ResponseTraits;

class UserInfoService
{
    use ResponseTraits;

    protected TransactionRepositoryInterface $transactionRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Extrait l'ID du compte depuis les abilities du token
     */
    public function extractCompteIdFromToken(Model $user): ?string
    {
        $token = $user->currentAccessToken();
        if (!$token || !isset($token->abilities)) {
            return null;
        }

        foreach ($token->abilities as $ability) {
            if (str_starts_with($ability, 'compte_id:')) {
                return str_replace('compte_id:', '', $ability);
            }
        }

        return null;
    }

    /**
     * Extrait le numéro de téléphone depuis les abilities du token
     */
    public function extractNumeroTelephoneFromToken(Model $user): ?string
    {
        $token = $user->currentAccessToken();
        if (!$token || !isset($token->abilities)) {
            return null;
        }

        foreach ($token->abilities as $ability) {
            if (str_starts_with($ability, 'numero_telephone:')) {
                return str_replace('numero_telephone:', '', $ability);
            }
        }

        return null;
    }

    /**
     * Trouve le compte de l'utilisateur
     */
    public function findUserCompte(Model $user): ?Compte
    {
        $compteId = $this->extractCompteIdFromToken($user);
        $numeroTelephone = $this->extractNumeroTelephoneFromToken($user);

        // Si on a un numéro de téléphone mais pas d'ID compte, chercher le compte
        if (!$compteId && $numeroTelephone) {
            $compte = Compte::where('numeroTelephone', $numeroTelephone)->first();
            if ($compte) {
                return $compte;
            }
        }

        // Si on a un ID compte, le récupérer directement
        if ($compteId) {
            return Compte::find($compteId);
        }

        // Sinon, essayer de récupérer le premier compte de l'utilisateur
        return $user->compte ?? null;
    }

    /**
     * Récupère les 10 dernières transactions du compte
     */
    public function getLastTenTransactions(Compte $compte): array
    {
        $transactions = $this->transactionRepository->getTransactionsForUser($compte->numeroTelephone);

        return $transactions->take(10)->map(function ($transaction) use ($compte) {
            return [
                'id' => $transaction->id,
                'type_transaction' => $transaction->type_transaction,
                'montant' => $transaction->montant,
                'date' => $transaction->date->toISOString(),
                'reference' => $transaction->reference,
                'contrepartie' => $transaction->expediteur === $compte->numeroTelephone
                    ? $transaction->destinataire
                    : $transaction->expediteur,
                'direction' => $transaction->expediteur === $compte->numeroTelephone ? 'debit' : 'credit'
            ];
        })->values()->toArray();
    }

    /**
     * Formate les informations utilisateur pour la réponse API
     */
    public function formatUserInfo(Compte $compte): array
    {
        return [
            'user' => [
                'id' => $compte->user->id,
                'nom' => $compte->user->nom,
                'prenom' => $compte->user->prenom,
                'role' => $compte->user->role,
            ],
            'compte' => [
                'id' => $compte->id,
                'numero_compte' => $compte->numeroCompte,
                'numero_telephone' => $compte->numeroTelephone,
                'type' => $compte->type,
                'statut' => $compte->statut,
                'date_creation' => $compte->dateCreation,
            ],
            'dernieres_transactions' => $this->getLastTenTransactions($compte)
        ];
    }

    /**
     * Récupère toutes les informations de l'utilisateur authentifié
     */
    public function getUserInfo(Model $user)
    {
        $compte = $this->findUserCompte($user);

        if (!$compte) {
            return $this->errorResponse('Informations de compte manquantes', 'missing_account_info', 400);
        }

        // Recharger le compte avec les relations
        $compte = Compte::with('user')->find($compte->id);

        if (!$compte) {
            return $this->errorResponse('Compte non trouvé', 'account_not_found', 404);
        }

        return $this->successResponse('Informations récupérées', $this->formatUserInfo($compte));
    }
}
<?php
namespace App\Services;

use App\Repositories\CompteRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\AuthenticationException;

class AuthService
{
    protected $compteRepo;

    public function __construct(CompteRepository $compteRepo)
    {
        $this->compteRepo = $compteRepo;
    }

    public function authenticate(string $numeroTelephone, string $codePing)
    {
        // Normaliser le numéro de téléphone pour ajouter +221 si nécessaire
        $numeroTelephone = $this->normalizePhoneNumber($numeroTelephone);

        $compte = $this->compteRepo->findByNumeroTelephone($numeroTelephone);

        if (!$compte || !Hash::check($codePing, $compte->codePing)) {
            throw new AuthenticationException('Numéro de téléphone ou code PIN invalide');
        }

        if ($compte->statut !== 'actif') {
            throw new \Exception('Votre compte n\'est pas actif');
        }

        $token = $compte->user->createToken('Personal Access Token', ['compte_id:' . $compte->id])->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $compte->user,
            'compte_id' => $compte->id,
            'compte' => $compte,
            'role' => $compte->user->role,
            'permissions' => $this->getPermissionsForRole($compte->user->role),
        ];
    }

    protected function getPermissionsForRole(string $role)
    {
        // Implémentez la récupération des permissions selon le rôle
        return [];
    }

    /**
     * Normalise le numéro de téléphone pour ajouter +221 si nécessaire
     */
    public function normalizePhoneNumber(string $numeroTelephone): string
    {
        // Supprimer tous les espaces et caractères non numériques sauf +
        $numeroTelephone = preg_replace('/[^\d+]/', '', $numeroTelephone);

        // Si le numéro commence par +221, il est déjà valide
        if (str_starts_with($numeroTelephone, '+221')) {
            return $numeroTelephone;
        }

        // Si le numéro commence par 221, ajouter +
        if (str_starts_with($numeroTelephone, '221')) {
            return '+' . $numeroTelephone;
        }

        // Si le numéro a 9 chiffres, ajouter +221
        if (strlen($numeroTelephone) === 9 && is_numeric($numeroTelephone)) {
            return '+221' . $numeroTelephone;
        }

        // Si le numéro a 12 chiffres et commence par 221, ajouter +
        if (strlen($numeroTelephone) === 12 && str_starts_with($numeroTelephone, '221')) {
            return '+' . $numeroTelephone;
        }

        // Retourner tel quel si aucune transformation n'est possible
        return $numeroTelephone;
    }
}

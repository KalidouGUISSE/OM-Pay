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
}

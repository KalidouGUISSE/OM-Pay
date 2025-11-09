<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compte;
use Illuminate\Support\Facades\Hash;
use App\Traits\ResponseTraits;

class AuthController extends Controller
{
    use ResponseTraits;

    public function login(Request $request)
    {
        $request->validate([
            'numeroTelephone' => 'required|string',
            'codePing' => 'required|string|min:4',
        ]);

        $compte = Compte::where('numeroTelephone', $request->numeroTelephone)->first();

        if (!$compte || !Hash::check($request->codePing, $compte->codePing)) {
            return $this->errorResponse('Numéro de téléphone ou code PIN invalide', 'auth_failed', 401);
        }

        if ($compte->statut !== 'actif') {
            return $this->errorResponse('Votre compte n\'est pas actif', 'account_inactive', 403);
        }

        // Créer le token avec Sanctum
        $token = $compte->user->createToken('Personal Access Token')->plainTextToken;

        // Créer la réponse avec les informations demandées
        $tokenData = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $compte->user,
            'compte_id' => $compte->id, // ID du compte avec lequel l'utilisateur s'est connecté
            'compte' => $compte, // Informations complètes du compte
            'role' => $compte->user->role,
            'permissions' => $this->getPermissionsForRole($compte->user->role),
        ];

        return $this->successResponse('Connexion réussie', $tokenData);
    }

    /**
     * Rafraîchir le token d'accès
     */
    public function refresh(Request $request)
    {
        // Cette fonctionnalité nécessite une implémentation plus avancée
        // Pour l'instant, retourner une erreur
        return $this->errorResponse('Fonctionnalité non implémentée', 'not_implemented', 501);
    }

    /**
     * Déconnexion - invalider le token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse('Déconnexion réussie', []);
    }

    private function getPermissionsForRole($role)
    {
        $rolePermissions = [
            'admin' => ['create', 'read', 'update', 'delete', 'manage_users'],
            'client' => ['read', 'update_own'],
        ];

        return $rolePermissions[$role] ?? [];
    }
}

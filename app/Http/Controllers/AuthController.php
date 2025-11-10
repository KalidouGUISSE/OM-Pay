<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;


use Illuminate\Http\Request;
use App\Models\Compte;
use Illuminate\Support\Facades\Hash;
use App\Traits\ResponseTraits;

class AuthController extends Controller
{
    use ResponseTraits;
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        try {
            $tokenData = $this->authService->authenticate($request->numeroTelephone, $request->codePing);
            return $this->successResponse('Connexion réussie', $tokenData);
        } catch (AuthenticationException $e) {
            return $this->errorResponse($e->getMessage(), 'auth_failed', 401);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'account_inactive', 403);
        }
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

<?php

namespace App\Http\Controllers;

use App\Http\Requests\InitiateLoginRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Services\UserInfoService;
use Illuminate\Auth\AuthenticationException;


use Illuminate\Http\Request;
use App\Models\Compte;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Traits\ResponseTraits;

class AuthController extends Controller
{
    use ResponseTraits;
    protected $authService;
    protected $userInfoService;

    public function __construct(AuthService $authService, UserInfoService $userInfoService)
    {
        $this->authService = $authService;
        $this->userInfoService = $userInfoService;
    }

    /**
     * Initie la connexion en générant un OTP
     */
    public function initiateLogin(InitiateLoginRequest $request)
    {
        Log::info('Début initiation login', ['numero' => $request->numeroTelephone]);

        try {
            $result = $this->authService->initiateLogin($request->numeroTelephone);
            Log::info('Initiation login réussie', ['numero' => $request->numeroTelephone]);
            return $this->successResponse('OTP envoyé avec succès', $result);
        } catch (AuthenticationException $e) {
            Log::error('Échec initiation login - AuthenticationException', [
                'numero' => $request->numeroTelephone,
                'message' => $e->getMessage()
            ]);
            return $this->errorResponse($e->getMessage(), 'auth_failed', 401);
        } catch (\Exception $e) {
            Log::error('Échec initiation login - Exception générale', [
                'numero' => $request->numeroTelephone,
                'message' => $e->getMessage()
            ]);
            return $this->errorResponse($e->getMessage(), 'account_inactive', 403);
        }
    }

    /**
     * Vérifie l'OTP et complète l'authentification
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        Log::info('Début vérification OTP', [
            'token_provided' => !empty($request->token),
            'otp_provided' => !empty($request->otp),
            'token_length' => strlen($request->token ?? ''),
            'otp_length' => strlen($request->otp ?? '')
        ]);

        try {
            $tokenData = $this->authService->verifyOtp($request->token, $request->otp);
            Log::info('Vérification OTP réussie');
            return $this->successResponse('Authentification réussie', $tokenData);
        } catch (AuthenticationException $e) {
            Log::error('Échec vérification OTP - AuthenticationException', [
                'message' => $e->getMessage(),
                'token' => $request->token,
                'otp' => $request->otp
            ]);
            return $this->errorResponse($e->getMessage(), 'otp_invalid', 401);
        } catch (\Exception $e) {
            Log::error('Échec vérification OTP - Exception générale', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Erreur lors de la vérification', 'verification_error', 500);
        }
    }

    /**
     * Retourne les informations de l'utilisateur authentifié
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Utilisateur non authentifié', 'unauthenticated', 401);
        }

        return $this->userInfoService->getUserInfo($user);
    }

    /**
     * Ancienne méthode de connexion (maintenue pour compatibilité)
     */
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
     * Déconnexion - avec Passport JWT, les tokens sont stateless
     * Le client doit simplement supprimer le token localement
     */
    public function logout(Request $request)
    {
        // Avec Passport JWT, pas de révocation côté serveur nécessaire
        // Le client gère la suppression du token localement
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

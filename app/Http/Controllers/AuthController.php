<?php

namespace App\Http\Controllers;

use App\Http\Requests\InitiateLoginRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Repositories\TransactionRepository;
use Illuminate\Auth\AuthenticationException;


use Illuminate\Http\Request;
use App\Models\Compte;
use Illuminate\Support\Facades\Hash;
use App\Traits\ResponseTraits;

class AuthController extends Controller
{
    use ResponseTraits;
    protected $authService;
    protected $transactionRepository;

    public function __construct(AuthService $authService, TransactionRepository $transactionRepository)
    {
        $this->authService = $authService;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Initie la connexion en générant un OTP
     */
    public function initiateLogin(InitiateLoginRequest $request)
    {
        try {
            $result = $this->authService->initiateLogin($request->numeroTelephone);
            return $this->successResponse('OTP envoyé avec succès', $result);
        } catch (AuthenticationException $e) {
            return $this->errorResponse($e->getMessage(), 'auth_failed', 401);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'account_inactive', 403);
        }
    }

    /**
     * Vérifie l'OTP et complète l'authentification
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        try {
            $tokenData = $this->authService->verifyOtp($request->token, $request->otp);
            return $this->successResponse('Authentification réussie', $tokenData);
        } catch (AuthenticationException $e) {
            return $this->errorResponse($e->getMessage(), 'otp_invalid', 401);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la vérification', 'verification_error', 500);
        }
    }

    /**
     * Retourne les informations de l'utilisateur authentifié
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Utilisateur non authentifié', 'unauthenticated', 401);
            }

            // Essayer de récupérer les informations du compte depuis les abilities du token
            $compteId = null;
            $numeroTelephone = null;

            $token = $user->currentAccessToken();
            if ($token && isset($token->abilities)) {
                foreach ($token->abilities as $ability) {
                    if (str_starts_with($ability, 'compte_id:')) {
                        $compteId = str_replace('compte_id:', '', $ability);
                    }
                    if (str_starts_with($ability, 'numero_telephone:')) {
                        $numeroTelephone = str_replace('numero_telephone:', '', $ability);
                    }
                }
            }

            // Si on a un numéro de téléphone mais pas d'ID compte, chercher le compte
            if (!$compteId && $numeroTelephone) {
                $compte = \App\Models\Compte::where('numeroTelephone', $numeroTelephone)->first();
                if ($compte) {
                    $compteId = $compte->id;
                }
            }

            // Si on n'a toujours pas d'ID compte, essayer de récupérer le premier compte de l'utilisateur
            if (!$compteId) {
                $compte = $user->compte;
                if ($compte) {
                    $compteId = $compte->id;
                }
            }

            if (!$compteId) {
                return $this->errorResponse('Informations de compte manquantes', 'missing_account_info', 400);
            }

            // Récupérer le compte depuis la base de données
            $compte = \App\Models\Compte::with('user')->find($compteId);

            if (!$compte) {
                return $this->errorResponse('Compte non trouvé', 'account_not_found', 404);
            }

            // Récupérer les 10 dernières transactions du compte
            $transactions = $this->transactionRepository->getTransactionsForUser($compte->numeroTelephone);
            $lastTenTransactions = $transactions->take(10)->map(function ($transaction) use ($compte) {
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
            });

            return $this->successResponse('Informations récupérées', [
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
                'dernieres_transactions' => $lastTenTransactions->values()
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la récupération des informations', 'info_error', 500);
        }
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

<?php
namespace App\Services;

use App\Contracts\Interfaces\CompteRepositoryInterface;
use App\Models\OtpVerification;
use App\Services\SmsService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuthService
{
    protected $compteRepo;
    protected $smsService;

    public function __construct(CompteRepositoryInterface $compteRepo, SmsService $smsService)
    {
        $this->compteRepo = $compteRepo;
        $this->smsService = $smsService;
    }

    /**
     * Initie l'authentification en générant un OTP et un token temporaire
     */
    public function initiateLogin(string $numeroTelephone)
    {
        // Normaliser le numéro de téléphone
        $numeroTelephone = $this->normalizePhoneNumber($numeroTelephone);

        // Vérifier si le compte existe
        $compte = $this->compteRepo->findByNumeroTelephone($numeroTelephone);

        if (!$compte) {
            throw new AuthenticationException('Numéro de téléphone non trouvé');
        }

        if ($compte->statut !== 'actif') {
            throw new \Exception('Votre compte n\'est pas actif');
        }

        // Créer l'OTP
        $otp = OtpVerification::createForPhone($numeroTelephone);

        // Générer un token temporaire contenant le numéro de téléphone et l'ID OTP
        $tempToken = Crypt::encryptString(json_encode([
            'numero_telephone' => $numeroTelephone,
            'otp_id' => $otp->id,
            'expires_at' => Carbon::now()->addMinutes(5)->toISOString(),
        ]));

        return [
            'temp_token' => $tempToken,
            'otp' => $otp->otp_code, // Afficher l'OTP généré pour les tests/développement
            'message' => 'OTP envoyé avec succès',
            'expires_in' => 300, // 5 minutes en secondes
        ];
    }

    /**
     * Vérifie l'OTP et génère le token d'authentification complet
     */
    public function verifyOtp(string $tempToken, string $otpCode)
    {
        try {
            // Log de débogage pour la config
            Log::info('Config app pour débogage', [
                'key_length' => strlen(config('app.key')),
                'cipher' => config('app.cipher'),
                'env' => config('app.env'),
                'token_length' => strlen($tempToken)
            ]);

            // Décrypter le token temporaire
            try {
                $tempData = json_decode(Crypt::decryptString($tempToken), true);
                Log::info('Déchiffrement réussi', ['tempData_keys' => array_keys($tempData ?? [])]);
            } catch (\Exception $e) {
                Log::error('Erreur déchiffrement', ['error' => $e->getMessage(), 'token' => $tempToken]);
                throw new AuthenticationException('Token temporaire invalide');
            }

            if (!$tempData || !isset($tempData['numero_telephone']) || !isset($tempData['otp_id'])) {
                Log::error('Token temporaire invalide', ['tempData' => $tempData]);
                throw new AuthenticationException('Token temporaire invalide');
            }

            // Vérifier l'expiration du token temporaire
            if (Carbon::parse($tempData['expires_at'])->isPast()) {
                Log::error('Token temporaire expiré', ['expires_at' => $tempData['expires_at']]);
                throw new AuthenticationException('Token temporaire expiré');
            }

            $numeroTelephone = $tempData['numero_telephone'];
            $otpId = $tempData['otp_id'];
            Log::info('Vérification OTP', [
                'numero_telephone' => $numeroTelephone,
                'otp_id' => $otpId,
                'otp_code' => $otpCode
            ]);

            // Trouver l'OTP spécifique par ID
            $otp = OtpVerification::where('id', $otpId)
                ->where('numero_telephone', $numeroTelephone)
                ->where('used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();
    
                Log::info('OTP trouvé', [
                    'otp_exists' => $otp ? true : false,
                    'otp_code_stored' => $otp ? $otp->otp_code : null,
                    'otp_used' => $otp ? $otp->used : null,
                    'otp_expires_at' => $otp ? $otp->expires_at : null
                ]);

            if (!$otp || !$otp->isValid($otpCode)) {
                Log::error('OTP invalide', [
                    'otp_exists' => $otp ? true : false,
                    'is_valid' => $otp ? $otp->isValid($otpCode) : false
                ]);
                throw new AuthenticationException('Code OTP invalide ou expiré');
            }

            // Marquer l'OTP comme utilisé
            $otp->markAsUsed();
            Log::info('OTP marqué comme utilisé', ['otp_id' => $otp->id]);

            // Récupérer le compte et procéder à l'authentification complète
            $compte = $this->compteRepo->findByNumeroTelephone($numeroTelephone);
            Log::info('Compte trouvé', ['compte_exists' => $compte ? true : false, 'numero' => $numeroTelephone]);

            if (!$compte || $compte->statut !== 'actif') {
                Log::error('Compte invalide', ['compte' => $compte, 'statut' => $compte ? $compte->statut : null]);
                throw new AuthenticationException('Compte non trouvé ou inactif');
            }

            Log::info('Création des tokens', ['compte_id' => $compte->id, 'user_exists' => $compte->user ? true : false, 'user_id' => $compte->user ? $compte->user->id : null]);

            // Vérifier si le client Passport existe, sinon le créer
            $personalAccessClient = \Laravel\Passport\Client::where('personal_access_client', true)->first();

            if (!$personalAccessClient) {
                Log::info('Création du client Passport Personal Access manquant');
                $personalAccessClient = \Laravel\Passport\Client::create([
                    'name' => 'OM Pay Personal Access Client',
                    'secret' => 'j53MIggdnwhdb7nlgH1LIV9N9sI98FKtTQkLA5DW',
                    'redirect' => 'http://localhost',
                    'personal_access_client' => true,
                    'password_client' => false,
                    'revoked' => false,
                ]);
                Log::info('Client Passport Personal Access créé', ['client_id' => $personalAccessClient->id]);
            } else {
                Log::info('Client Passport Personal Access trouvé', ['client_id' => $personalAccessClient->id]);
            }

            try {
                Log::info('Début création tokens', ['user_id' => $compte->user->id]);
                // Créer un token d'accès avec Passport
                $accessToken = $compte->user->createToken('Personal Access Token');
                $token = $accessToken->accessToken;

                // Créer un refresh token séparé
                $refreshTokenObj = $compte->user->createToken('Refresh Token');
                $refreshToken = $refreshTokenObj->accessToken;

                Log::info('Tokens créés avec succès', [
                    'access_token_length' => strlen($token),
                    'refresh_token_length' => strlen($refreshToken),
                    'access_token_id' => $accessToken->token->id,
                    'refresh_token_id' => $refreshTokenObj->token->id
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur création tokens', [
                    'error' => $e->getMessage(),
                    'user_id' => $compte->user->id,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

            return [
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600, // 1 heure pour l'access token
            ];

        } catch (\Exception $e) {
            throw new AuthenticationException($e->getMessage());
        }
    }

    /**
     * Ancienne méthode d'authentification (maintenue pour compatibilité)
     */
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

        $token = $compte->user->createToken('Personal Access Token', [
            'compte_id:' . $compte->id,
            'numero_telephone:' . $compte->numeroTelephone
        ])->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $compte->user,
            'compte_id' => $compte->id,
            'numero_telephone' => $compte->numeroTelephone,
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

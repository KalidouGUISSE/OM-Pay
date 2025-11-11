<?php
namespace App\Services;

use App\Repositories\CompteRepository;
use App\Models\OtpVerification;
use App\Services\SmsService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class AuthService
{
    protected $compteRepo;
    protected $smsService;

    public function __construct(CompteRepository $compteRepo, SmsService $smsService)
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

        // Générer un token temporaire contenant le numéro de téléphone
        $tempToken = Crypt::encryptString(json_encode([
            'numero_telephone' => $numeroTelephone,
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
            // Décrypter le token temporaire
            $tempData = json_decode(Crypt::decryptString($tempToken), true);

            if (!$tempData || !isset($tempData['numero_telephone'])) {
                throw new AuthenticationException('Token temporaire invalide');
            }

            // Vérifier l'expiration du token temporaire
            if (Carbon::parse($tempData['expires_at'])->isPast()) {
                throw new AuthenticationException('Token temporaire expiré');
            }

            $numeroTelephone = $tempData['numero_telephone'];

            // Trouver l'OTP valide
            $otp = OtpVerification::where('numero_telephone', $numeroTelephone)
                ->where('used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$otp || !$otp->isValid($otpCode)) {
                throw new AuthenticationException('Code OTP invalide ou expiré');
            }

            // Marquer l'OTP comme utilisé
            $otp->markAsUsed();

            // Récupérer le compte et procéder à l'authentification complète
            $compte = $this->compteRepo->findByNumeroTelephone($numeroTelephone);

            if (!$compte || $compte->statut !== 'actif') {
                throw new AuthenticationException('Compte non trouvé ou inactif');
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

        } catch (\Exception $e) {
            throw new AuthenticationException('Erreur lors de la vérification OTP');
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

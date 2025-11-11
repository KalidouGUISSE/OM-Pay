<?php

namespace App\Services;

use App\Contracts\Sms\SmsProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * Service SMS refactorisé pour respecter les principes SOLID
 * Utilise l'injection de dépendance pour le fournisseur SMS
 */
class SmsService
{
    protected SmsProviderInterface $smsProvider;

    public function __construct(SmsProviderInterface $smsProvider)
    {
        $this->smsProvider = $smsProvider;
    }

    /**
     * Envoie un SMS avec le code OTP
     * Utilise le fournisseur injecté pour l'envoi
     */
    public function sendOtpSms(string $to, string $otpCode): bool
    {
        $message = "Code OTP {$otpCode} valable 5min - OM Pay";

        $result = $this->smsProvider->sendSms($to, $message);

        if ($result) {
            Log::info("SMS OTP envoyé avec succès via {$this->smsProvider->getProviderName()}", [
                'to' => $to,
                'otp_length' => strlen($otpCode),
                'provider' => $this->smsProvider->getProviderName()
            ]);
        } else {
            Log::error("Échec de l'envoi du SMS OTP via {$this->smsProvider->getProviderName()}", [
                'to' => $to,
                'otp_length' => strlen($otpCode),
                'provider' => $this->smsProvider->getProviderName()
            ]);
        }

        return $result;
    }

    /**
     * Envoie un SMS générique
     * Méthode publique pour permettre l'envoi de messages personnalisés
     */
    public function sendSms(string $to, string $message): bool
    {
        return $this->smsProvider->sendSms($to, $message);
    }

    /**
     * Vérifie si le fournisseur SMS est configuré
     */
    public function isProviderConfigured(): bool
    {
        return $this->smsProvider->isConfigured();
    }

    /**
     * Retourne les informations du fournisseur actuel
     */
    public function getProviderInfo(): array
    {
        return $this->smsProvider->getConfigurationInfo();
    }

    /**
     * Change le fournisseur SMS (pour les tests ou changements dynamiques)
     */
    public function setSmsProvider(SmsProviderInterface $smsProvider): void
    {
        $this->smsProvider = $smsProvider;
    }
}
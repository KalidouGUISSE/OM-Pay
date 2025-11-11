<?php

namespace App\Contracts\Sms;

/**
 * Interface pour les fournisseurs de services SMS
 * Respecte le principe Open-Closed (SOLID)
 */
interface SmsProviderInterface
{
    /**
     * Envoie un SMS à un numéro de téléphone
     *
     * @param string $to Numéro de téléphone destinataire
     * @param string $message Contenu du message
     * @return bool True si l'envoi a réussi, false sinon
     */
    public function sendSms(string $to, string $message): bool;

    /**
     * Vérifie si le fournisseur est configuré et opérationnel
     *
     * @return bool True si le fournisseur est prêt à envoyer des SMS
     */
    public function isConfigured(): bool;

    /**
     * Retourne le nom du fournisseur
     *
     * @return string Nom du fournisseur (ex: "twilio", "aws-sns", etc.)
     */
    public function getProviderName(): string;

    /**
     * Retourne les informations de configuration actuelles (sans données sensibles)
     *
     * @return array Informations de configuration masquées
     */
    public function getConfigurationInfo(): array;
}
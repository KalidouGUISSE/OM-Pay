<?php

namespace App\Services\Sms\Providers;

use App\Contracts\Sms\SmsProviderInterface;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Illuminate\Support\Facades\Log;

/**
 * Implémentation Twilio du fournisseur SMS
 * Respecte le principe Open-Closed en implémentant SmsProviderInterface
 */
class TwilioSmsProvider implements SmsProviderInterface
{
    private Client $twilio;
    private array $config;

    public function __construct()
    {
        $this->config = [
            'sid' => config('services.twilio.sid'),
            'token' => config('services.twilio.token'),
            'from' => config('services.twilio.from'),
            'messaging_service_sid' => 'MG9069248137b24d15bc2529413d6e7543'
        ];

        if ($this->isConfigured()) {
            $this->twilio = new Client($this->config['sid'], $this->config['token']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendSms(string $to, string $message): bool
    {
        if (!$this->isConfigured()) {
            Log::error('Twilio non configuré', $this->getConfigurationInfo());
            return false;
        }

        try {
            $this->twilio->messages->create($to, [
                'from' => $this->config['from'],
                'body' => $message,
                'messagingServiceSid' => $this->config['messaging_service_sid']
            ]);

            Log::info('SMS envoyé avec succès via Twilio', [
                'to' => $to,
                'provider' => $this->getProviderName(),
                'message_length' => strlen($message)
            ]);

            return true;

        } catch (TwilioException $e) {
            Log::error('Erreur Twilio lors de l\'envoi SMS', [
                'to' => $to,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'provider' => $this->getProviderName()
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Exception inattendue lors de l\'envoi SMS Twilio', [
                'to' => $to,
                'error' => $e->getMessage(),
                'provider' => $this->getProviderName()
            ]);

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isConfigured(): bool
    {
        return !empty($this->config['sid']) &&
               !empty($this->config['token']) &&
               !empty($this->config['from']);
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderName(): string
    {
        return 'twilio';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationInfo(): array
    {
        return [
            'provider' => $this->getProviderName(),
            'configured' => $this->isConfigured(),
            'has_sid' => !empty($this->config['sid']),
            'has_token' => !empty($this->config['token']),
            'has_from' => !empty($this->config['from']),
            'from_number' => $this->maskPhoneNumber($this->config['from'] ?? ''),
            'messaging_service_configured' => !empty($this->config['messaging_service_sid'])
        ];
    }

    /**
     * Masque partiellement un numéro de téléphone pour les logs
     */
    private function maskPhoneNumber(string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Garde les 4 premiers et 3 derniers caractères
        return substr($phone, 0, 4) . '****' . substr($phone, -3);
    }
}
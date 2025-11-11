<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    /**
     * Envoie un SMS avec le code OTP
     */
    public function sendOtpSms(string $to, string $otpCode): bool
    {
        try {
            $message = "Code OTP {$otpCode} valable 5min - OM Pay";

            $this->twilio->messages->create(
                $to,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $message,
                    'messagingServiceSid' => 'MG9069248137b24d15bc2529413d6e7543'
                ]
            );

            Log::info("OTP SMS envoyé avec succès", [
                'to' => $to,
                'otp_length' => strlen($otpCode)
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi du SMS OTP", [
                'to' => $to,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
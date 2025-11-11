<?php

namespace App\Observers;

use App\Models\OtpVerification;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

class OtpVerificationObserver
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Handle the OtpVerification "created" event.
     * Envoie automatiquement un SMS avec le code OTP lors de la création
     */
    public function created(OtpVerification $otpVerification): void
    {
        try {
            $success = $this->smsService->sendOtpSms(
                $otpVerification->numero_telephone,
                $otpVerification->otp_code
            );

            if ($success) {
                Log::info("SMS OTP envoyé avec succès", [
                    'numero_telephone' => $otpVerification->numero_telephone,
                    'otp_id' => $otpVerification->id
                ]);
            } else {
                Log::error("Échec de l'envoi du SMS OTP", [
                    'numero_telephone' => $otpVerification->numero_telephone,
                    'otp_id' => $otpVerification->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Exception lors de l'envoi du SMS OTP", [
                'numero_telephone' => $otpVerification->numero_telephone,
                'otp_id' => $otpVerification->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the OtpVerification "updated" event.
     */
    public function updated(OtpVerification $otpVerification): void
    {
        //
    }

    /**
     * Handle the OtpVerification "deleted" event.
     */
    public function deleted(OtpVerification $otpVerification): void
    {
        //
    }

    /**
     * Handle the OtpVerification "restored" event.
     */
    public function restored(OtpVerification $otpVerification): void
    {
        //
    }

    /**
     * Handle the OtpVerification "force deleted" event.
     */
    public function forceDeleted(OtpVerification $otpVerification): void
    {
        //
    }
}

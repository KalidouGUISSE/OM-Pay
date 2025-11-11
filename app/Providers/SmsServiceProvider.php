<?php

namespace App\Providers;

use App\Contracts\Sms\SmsProviderInterface;
use App\Services\Sms\Providers\TwilioSmsProvider;
use App\Services\SmsService;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Enregistrer le fournisseur SMS Twilio
        $this->app->bind(SmsProviderInterface::class, TwilioSmsProvider::class);

        // Enregistrer le service SMS
        $this->app->singleton(SmsService::class, function ($app) {
            return new SmsService($app->make(SmsProviderInterface::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

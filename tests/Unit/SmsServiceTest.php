<?php

namespace Tests\Unit;

use App\Services\SmsService;
use App\Contracts\Sms\SmsProviderInterface;
use Tests\TestCase;
use Mockery;

class SmsServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test l'envoi réussi d'un SMS OTP
     */
    public function test_send_otp_sms_success(): void
    {
        // Créer un mock du fournisseur SMS
        $smsProviderMock = Mockery::mock(SmsProviderInterface::class);
        $smsProviderMock->shouldReceive('sendSms')
            ->once()
            ->with('+221781157773', 'Code OTP 123456 valable 5min - OM Pay')
            ->andReturn(true);
        $smsProviderMock->shouldReceive('getProviderName')
            ->andReturn('mock-provider');

        // Créer le service avec le mock
        $smsService = new SmsService($smsProviderMock);

        // Tester l'envoi
        $result = $smsService->sendOtpSms('+221781157773', '123456');

        $this->assertTrue($result);
    }

    /**
     * Test l'échec de l'envoi d'un SMS OTP
     */
    public function test_send_otp_sms_failure(): void
    {
        // Créer un mock du fournisseur SMS qui échoue
        $smsProviderMock = Mockery::mock(SmsProviderInterface::class);
        $smsProviderMock->shouldReceive('sendSms')
            ->once()
            ->andReturn(false);
        $smsProviderMock->shouldReceive('getProviderName')
            ->andReturn('mock-provider');

        // Créer le service avec le mock
        $smsService = new SmsService($smsProviderMock);

        // Tester l'envoi
        $result = $smsService->sendOtpSms('+221781157773', '123456');

        $this->assertFalse($result);
    }

    /**
     * Test l'envoi d'un SMS générique
     */
    public function test_send_generic_sms(): void
    {
        $smsProviderMock = Mockery::mock(SmsProviderInterface::class);
        $smsProviderMock->shouldReceive('sendSms')
            ->once()
            ->with('+221781157773', 'Hello World')
            ->andReturn(true);

        $smsService = new SmsService($smsProviderMock);

        $result = $smsService->sendSms('+221781157773', 'Hello World');

        $this->assertTrue($result);
    }

    /**
     * Test la vérification de la configuration du fournisseur
     */
    public function test_is_provider_configured(): void
    {
        $smsProviderMock = Mockery::mock(SmsProviderInterface::class);
        $smsProviderMock->shouldReceive('isConfigured')
            ->andReturn(true);

        $smsService = new SmsService($smsProviderMock);

        $this->assertTrue($smsService->isProviderConfigured());
    }

    /**
     * Test la récupération des informations du fournisseur
     */
    public function test_get_provider_info(): void
    {
        $expectedInfo = [
            'provider' => 'twilio',
            'configured' => true,
            'has_sid' => true
        ];

        $smsProviderMock = Mockery::mock(SmsProviderInterface::class);
        $smsProviderMock->shouldReceive('getConfigurationInfo')
            ->andReturn($expectedInfo);

        $smsService = new SmsService($smsProviderMock);

        $this->assertEquals($expectedInfo, $smsService->getProviderInfo());
    }

    /**
     * Test le changement dynamique de fournisseur SMS
     */
    public function test_set_sms_provider(): void
    {
        $initialProviderMock = Mockery::mock(SmsProviderInterface::class);
        $newProviderMock = Mockery::mock(SmsProviderInterface::class);

        $smsService = new SmsService($initialProviderMock);

        // Vérifier que le fournisseur initial est utilisé
        $initialProviderMock->shouldReceive('sendSms')->once()->andReturn(true);
        $result1 = $smsService->sendSms('+221781157773', 'Test');

        // Changer le fournisseur
        $smsService->setSmsProvider($newProviderMock);

        // Vérifier que le nouveau fournisseur est utilisé
        $newProviderMock->shouldReceive('sendSms')->once()->andReturn(false);
        $result2 = $smsService->sendSms('+221781157773', 'Test');

        $this->assertTrue($result1);
        $this->assertFalse($result2);
    }

    /**
     * Test que le message OTP est formaté correctement
     */
    public function test_otp_message_formatting(): void
    {
        $smsProviderMock = Mockery::mock(SmsProviderInterface::class);
        $smsProviderMock->shouldReceive('sendSms')
            ->once()
            ->with('+221781157773', 'Code OTP 987654 valable 5min - OM Pay')
            ->andReturn(true);
        $smsProviderMock->shouldReceive('getProviderName')
            ->andReturn('test-provider');

        $smsService = new SmsService($smsProviderMock);

        $result = $smsService->sendOtpSms('+221781157773', '987654');

        $this->assertTrue($result);
    }
}

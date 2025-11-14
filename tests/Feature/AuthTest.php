<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Compte;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_initiate_login()
    {
        // Créer un utilisateur et un compte de test
        $user = User::factory()->create();
        $compte = Compte::factory()->create([
            'id_client' => $user->id,
            'numeroTelephone' => '+221771234567',
            'codePing' => Hash::make('1234'),
            'statut' => 'actif',
        ]);

        $response = $this->postJson('/api/v1/auth/initiate-login', [
            'numeroTelephone' => '+221771234567',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'temp_token',
                        'message',
                        'expires_in',
                    ],
                ]);
    }

    /** @test */
    public function user_can_verify_otp_and_get_tokens()
    {
        // Créer un utilisateur et un compte de test
        $user = User::factory()->create();
        $compte = Compte::factory()->create([
            'id_client' => $user->id,
            'numeroTelephone' => '+221771234567',
            'codePing' => Hash::make('1234'),
            'statut' => 'actif',
        ]);

        // Simuler l'initiation de login pour obtenir le temp_token et l'OTP
        $initiateResponse = $this->postJson('/api/v1/auth/initiate-login', [
            'numeroTelephone' => '+221771234567',
        ]);

        $tempToken = $initiateResponse->json('data.temp_token');
        $otp = $initiateResponse->json('data.otp'); // OTP affiché pour les tests

        // Vérifier l'OTP
        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'token' => $tempToken,
            'otp' => $otp,
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'access_token',
                        'refresh_token',
                        'token_type',
                        'expires_in',
                    ],
                ]);
    }

    /** @test */
    public function authenticated_user_can_access_protected_route()
    {
        // Créer un utilisateur et un compte de test
        $user = User::factory()->create();
        $compte = Compte::factory()->create([
            'id_client' => $user->id,
            'numeroTelephone' => '+221771234567',
            'codePing' => Hash::make('1234'),
            'statut' => 'actif',
        ]);

        // Utiliser Passport::actingAs pour simuler l'authentification
        Passport::actingAs($user);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user',
                        'compte',
                    ],
                ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_route()
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }
}
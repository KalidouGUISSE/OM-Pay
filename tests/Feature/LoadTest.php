<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Compte;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Hash;

class LoadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_handle_multiple_authentication_requests()
    {
        // Créer plusieurs utilisateurs
        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $user = User::factory()->create();
            $compte = Compte::factory()->create([
                'id_client' => $user->id,
                'numeroTelephone' => '+22177' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'codePing' => Hash::make('1234'),
                'statut' => 'actif',
            ]);
            $users[] = $user;
        }

        // Tester l'initiation de login pour tous les utilisateurs
        $startTime = microtime(true);
        foreach ($users as $user) {
            $response = $this->postJson('/api/v1/auth/initiate-login', [
                'numeroTelephone' => $user->comptes()->first()->numeroTelephone,
            ]);
            $response->assertStatus(201);
        }
        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Vérifier que toutes les requêtes ont été traitées en moins de 5 secondes
        $this->assertLessThan(5.0, $duration, "Load test failed: took {$duration} seconds for 10 requests");
    }

    /** @test */
    public function can_handle_concurrent_balance_queries()
    {
        // Créer un utilisateur avec des transactions
        $user = User::factory()->create();
        $compte = Compte::factory()->create([
            'id_client' => $user->id,
            'numeroTelephone' => '+221771234567',
            'codePing' => Hash::make('1234'),
            'statut' => 'actif',
        ]);

        // Créer plusieurs transactions
        for ($i = 0; $i < 50; $i++) {
            \App\Models\Transaction::factory()->create([
                'expediteur' => '+221771234567',
                'destinataire' => '+221772345678',
                'montant' => 100,
            ]);
        }

        Passport::actingAs($user);

        // Tester plusieurs requêtes de solde
        $startTime = microtime(true);
        for ($i = 0; $i < 20; $i++) {
            $response = $this->getJson("/api/v1/compte/{$compte->numeroTelephone}/solde");
            $response->assertStatus(200);
        }
        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Vérifier que toutes les requêtes ont été traitées en moins de 10 secondes
        $this->assertLessThan(10.0, $duration, "Load test failed: took {$duration} seconds for 20 balance queries");
    }
}
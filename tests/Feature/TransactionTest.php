<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Compte;
use App\Models\Transaction;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Hash;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_get_account_balance()
    {
        // Créer des utilisateurs et comptes de test
        $user = User::factory()->create();
        $compte = Compte::factory()->create([
            'id_client' => $user->id,
            'numeroTelephone' => '+221771234567',
            'codePing' => Hash::make('1234'),
            'statut' => 'actif',
        ]);

        // Créer quelques transactions
        Transaction::factory()->create([
            'expediteur' => '+221771234567',
            'destinataire' => '+221772345678',
            'montant' => 1000,
            'type_transaction' => 'transfert',
        ]);

        Transaction::factory()->create([
            'expediteur' => '+221773456789',
            'destinataire' => '+221771234567',
            'montant' => 500,
            'type_transaction' => 'transfert',
        ]);

        Passport::actingAs($user);

        $response = $this->getJson("/api/v1/compte/{$compte->numeroTelephone}/solde");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'solde',
                        'numero_compte',
                    ],
                ]);
    }

    /** @test */
    public function authenticated_user_can_create_transaction()
    {
        // Créer des utilisateurs et comptes de test
        $user1 = User::factory()->create();
        $compte1 = Compte::factory()->create([
            'id_client' => $user1->id,
            'numeroTelephone' => '+221771234567',
            'codePing' => Hash::make('1234'),
            'statut' => 'actif',
        ]);

        $user2 = User::factory()->create();
        $compte2 = Compte::factory()->create([
            'id_client' => $user2->id,
            'numeroTelephone' => '+221772345678',
            'codePing' => Hash::make('5678'),
            'statut' => 'actif',
        ]);

        Passport::actingAs($user1);

        $transactionData = [
            'destinataire' => '+221772345678',
            'montant' => 1000,
            'type_transaction' => 'transfert',
        ];

        $response = $this->postJson("/api/v1/compte/{$compte1->numeroTelephone}/transactions", $transactionData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'type_transaction',
                        'montant',
                        'reference',
                    ],
                ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_transaction_endpoints()
    {
        $response = $this->getJson('/api/v1/compte/+221771234567/solde');

        $response->assertStatus(401);
    }
}
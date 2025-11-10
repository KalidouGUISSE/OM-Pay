<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Compte;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Récupérer deux comptes aléatoires différents pour expéditeur et destinataire
        $comptes = Compte::inRandomOrder()->take(2)->get();

        // S'assurer qu'on a au moins 2 comptes
        if ($comptes->count() < 2) {
            // Créer des comptes temporaires si nécessaire
            $compte1 = Compte::factory()->create();
            $compte2 = Compte::factory()->create();
        } else {
            $compte1 = $comptes[0];
            $compte2 = $comptes[1];
        }

        return [
            'id' => Str::uuid()->toString(),
            'type_transaction' => $this->faker->randomElement(['transfert', 'Transfert d\'argent']),
            'destinataire' => $compte1->numeroTelephone,
            'expediteur' => $compte2->numeroTelephone,
            'montant' => $this->faker->randomFloat(2, 100, 100000), // Entre 100 et 100000 FCFA
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'reference' => 'PP' . date('ym') . '.' . date('Y') . '.B' . strtoupper(Str::random(5)),
            'metadata' => [
                'derniereModification' => $this->faker->dateTime()->format('Y-m-d\TH:i:s\Z'),
                'version' => $this->faker->numberBetween(1, 5),
            ],
        ];
    }
}

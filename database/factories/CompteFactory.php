<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class CompteFactory extends Factory
{
    public function definition(): array
    {
        // On essaie d’obtenir un utilisateur existant
        $user = User::inRandomOrder()->first();

        // Si aucun utilisateur n’existe, on en crée un
        if (!$user) {
            $user = User::factory()->create();
        }

        return [
            'id' => Str::uuid()->toString(),
            'id_client' => $user->id, // toujours un UUID valide
            'numeroCompte' => strtoupper(Str::random(10)),
            'numeroTelephone' => '+221' . fake()->unique()->numberBetween(700000000, 799999999), // Numéro sénégalais valide
            'codePing' => Hash::make('1234'), // Code PIN par défaut pour les tests
            'codePingPlain' => '1234', // Pour les tests, stocker le code non haché
            'type' => fake()->randomElement(['simple', 'marchand']),
            'dateCreation' => now(),
            'statut' => fake()->randomElement(['actif', 'bloque', 'ferme']),
            'metadata' => [
                'derniereModification' => now()->toDateTimeString(),
                'version' => fake()->numberBetween(1, 10),
            ],
        ];
    }
}

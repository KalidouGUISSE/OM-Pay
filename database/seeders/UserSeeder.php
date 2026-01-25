<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Compte;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les utilisateurs spécifiques demandés
        $usersData = [
            [
                'nom' => 'Sams',
                'prenom' => 'Utilisateur',
                'role' => 'client',
                'numeroTelephone' => '+221781157773',
            ],
            [
                'nom' => 'Kalidou',
                'prenom' => 'Utilisateur',
                'role' => 'client',
                'numeroTelephone' => '+221784458786',
            ],
            [
                'nom' => 'Rama',
                'prenom' => 'Utilisateur',
                'role' => 'client',
                'numeroTelephone' => '+221771279062',
            ],
            [
                'nom' => 'Wane',
                'prenom' => 'Mr',
                'role' => 'client',
                'numeroTelephone' => '+221777669595',
            ],
        ];

        foreach ($usersData as $userData) {
            // Créer l'utilisateur
            $user = User::create([
                'nom' => $userData['nom'],
                'prenom' => $userData['prenom'],
                'role' => $userData['role'],
            ]);

            // Créer le compte associé
            Compte::create([
                'id_client' => $user->id,
                'numeroCompte' => 'NCMTP' . strtoupper(substr($userData['nom'], 0, 3)) . rand(100, 999),
                'numeroTelephone' => $userData['numeroTelephone'],
                'codePing' => bcrypt('1234'), // Code PIN par défaut
                'codePingPlain' => '1234',
                'type' => 'simple',
                'dateCreation' => now(),
                'statut' => 'actif',
                'metadata' => [
                    'derniereModification' => now()->toDateTimeString(),
                    'version' => 1,
                ],
            ]);
        }

        // Créer des utilisateurs génériques supplémentaires si nécessaire
        User::factory()
            ->count(2)
            ->has(Compte::factory()->count(1), 'comptes')
            ->create();
    }
}

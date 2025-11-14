<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SampleTransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les utilisateurs avec leurs comptes
        $users = User::with('comptes')->get();

        if ($users->isEmpty()) {
            $this->command->info('Aucun utilisateur trouvé. Veuillez exécuter les autres seeders d\'abord.');
            return;
        }

        $this->command->info('Initialisation des transactions d\'exemple...');

        $transactionTypes = [
            'Dépôt d\'argent',
            'Retrait d\'argent',
            'Transfert d\'argent',
            'Paiement marchand',
            'Recharge mobile'
        ];

        $totalTransactions = 0;

        foreach ($users as $user) {
            $comptes = $user->comptes;

            if ($comptes->isEmpty()) {
                $this->command->warn("Utilisateur {$user->nom} {$user->prenom} n'a pas de compte.");
                continue;
            }

            foreach ($comptes as $compte) {
                // Créer 5-10 transactions par compte
                $numTransactions = rand(5, 10);

                for ($i = 0; $i < $numTransactions; $i++) {
                    $type = $transactionTypes[array_rand($transactionTypes)];
                    $montant = $this->generateRealisticAmount($type);
                    $date = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

                    // Déterminer expéditeur et destinataire selon le type de transaction
                    switch ($type) {
                        case 'Dépôt d\'argent':
                            $expediteur = $this->getRandomDestinataire($compte->numeroTelephone); // Dépôt depuis une source externe
                            $destinataire = $compte->numeroTelephone;
                            break;
                        case 'Retrait d\'argent':
                            $expediteur = $compte->numeroTelephone;
                            $destinataire = $this->getRandomDestinataire($compte->numeroTelephone); // Retrait vers une destination externe
                            break;
                        case 'Transfert d\'argent':
                            $expediteur = $compte->numeroTelephone;
                            $destinataire = $this->getRandomDestinataire($compte->numeroTelephone);
                            break;
                        case 'Paiement marchand':
                            $expediteur = $compte->numeroTelephone;
                            $destinataire = $this->getRandomDestinataire($compte->numeroTelephone);
                            break;
                        case 'Recharge mobile':
                            $expediteur = $compte->numeroTelephone;
                            $destinataire = $this->getRandomDestinataire($compte->numeroTelephone);
                            break;
                        default:
                            $expediteur = $compte->numeroTelephone;
                            $destinataire = $this->getRandomDestinataire($compte->numeroTelephone);
                    }

                    $transaction = Transaction::create([
                        'id' => Str::uuid()->toString(),
                        'type_transaction' => $type,
                        'destinataire' => $destinataire,
                        'expediteur' => $expediteur,
                        'montant' => $montant,
                        'date' => $date,
                        'reference' => $this->generateReference(),
                        'metadata' => [
                            'derniereModification' => $date->toISOString(),
                            'version' => 1,
                            'description' => $type
                        ],
                    ]);

                    $totalTransactions++;
                    $this->command->info("Transaction créée: {$type} - {$montant} FCFA pour {$user->nom} {$user->prenom}");
                }
            }
        }

        $this->command->info("✅ Initialisation terminée ! {$totalTransactions} transactions créées pour " . $users->count() . " utilisateurs.");
    }

    /**
     * Génère un montant réaliste selon le type de transaction
     */
    private function generateRealisticAmount(string $type): float
    {
        return match ($type) {
            'Dépôt d\'argent' => rand(5000, 50000), // 5,000 - 50,000 FCFA
            'Retrait d\'argent' => rand(1000, 20000), // 1,000 - 20,000 FCFA
            'Transfert d\'argent' => rand(500, 25000), // 500 - 25,000 FCFA
            'Paiement marchand' => rand(100, 5000), // 100 - 5,000 FCFA
            'Recharge mobile' => rand(1000, 10000), // 1,000 - 10,000 FCFA
            default => rand(100, 10000)
        };
    }

    /**
     * Génère une référence unique pour la transaction
     */
    private function generateReference(): string
    {
        do {
            $reference = 'PP' . date('ym') . '.' . date('Y') . '.B' . strtoupper(Str::random(5));
        } while (Transaction::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Obtient un numéro de destinataire aléatoire différent du numéro actuel
     */
    private function getRandomDestinataire(string $currentNumero): string
    {
        $numeros = [
            '+221771234567',
            '+221772345678',
            '+221773456789',
            '+221774567890',
            '+221775678901',
            '+221776789012',
            '+221777890123',
            '+221778901234'
        ];

        // Éviter de retourner le même numéro
        $availableNumeros = array_filter($numeros, fn($num) => $num !== $currentNumero);

        return $availableNumeros[array_rand($availableNumeros)];
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SpecificAccountTransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targetPhone = '+221784458786';

        // Trouver le compte avec ce numéro de téléphone
        $compte = Compte::where('numeroTelephone', $targetPhone)->first();

        if (!$compte) {
            $this->command->error("Aucun compte trouvé avec le numéro {$targetPhone}");
            return;
        }

        $this->command->info("Initialisation des transactions pour le compte {$targetPhone}...");

        $transactionTypes = [
            'Dépôt d\'argent',
            'Retrait d\'argent',
            'Transfert d\'argent',
            'Paiement marchand',
            'Recharge mobile'
        ];

        $totalTransactions = 0;

        // Créer 15-20 transactions pour ce compte
        $numTransactions = rand(15, 20);

        for ($i = 0; $i < $numTransactions; $i++) {
            $type = $transactionTypes[array_rand($transactionTypes)];
            $montant = $this->generateRealisticAmount($type);
            $date = Carbon::now()->subDays(rand(0, 60))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            // Déterminer expéditeur et destinataire selon le type de transaction
            switch ($type) {
                case 'Dépôt d\'argent':
                    $expediteur = $this->getRandomExternalSource(); // Dépôt depuis une source externe
                    $destinataire = $compte->numeroTelephone;
                    break;
                case 'Retrait d\'argent':
                    $expediteur = $compte->numeroTelephone;
                    $destinataire = $this->getRandomExternalDestination(); // Retrait vers une destination externe
                    break;
                case 'Transfert d\'argent':
                    $expediteur = $compte->numeroTelephone;
                    $destinataire = $this->getRandomDestinataire($compte->numeroTelephone);
                    break;
                case 'Paiement marchand':
                    $expediteur = $compte->numeroTelephone;
                    $destinataire = $this->getRandomMerchant();
                    break;
                case 'Recharge mobile':
                    $expediteur = $compte->numeroTelephone;
                    $destinataire = $this->getRandomMobileNumber();
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
                    'description' => $type,
                    'compte_id' => $compte->id
                ],
            ]);

            $totalTransactions++;
            $this->command->info("Transaction créée: {$type} - {$montant} FCFA - {$expediteur} → {$destinataire}");
        }

        $this->command->info("✅ Initialisation terminée ! {$totalTransactions} transactions créées pour le compte {$targetPhone}");

        // Calculer et afficher le solde
        $solde = $this->calculateBalance($compte->numeroTelephone);
        $this->command->info("💰 Solde actuel du compte: {$solde} FCFA");
    }

    /**
     * Génère un montant réaliste selon le type de transaction
     */
    private function generateRealisticAmount(string $type): float
    {
        return match ($type) {
            'Dépôt d\'argent' => rand(10000, 100000), // 10,000 - 100,000 FCFA
            'Retrait d\'argent' => rand(5000, 50000), // 5,000 - 50,000 FCFA
            'Transfert d\'argent' => rand(1000, 50000), // 1,000 - 50,000 FCFA
            'Paiement marchand' => rand(500, 10000), // 500 - 10,000 FCFA
            'Recharge mobile' => rand(2000, 20000), // 2,000 - 20,000 FCFA
            default => rand(500, 25000)
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
            '+221778901234',
            '+221779012345',
            '+221780123456'
        ];

        // Éviter de retourner le même numéro
        $availableNumeros = array_filter($numeros, fn($num) => $num !== $currentNumero);

        return $availableNumeros[array_rand($availableNumeros)];
    }

    /**
     * Obtient une source externe pour les dépôts
     */
    private function getRandomExternalSource(): string
    {
        $sources = [
            'Banque Centrale',
            'Agence Western Union',
            'Transfert International',
            'Dépôt Espèces',
            'Virement Bancaire'
        ];

        return $sources[array_rand($sources)];
    }

    /**
     * Obtient une destination externe pour les retraits
     */
    private function getRandomExternalDestination(): string
    {
        $destinations = [
            'Retrait DAB',
            'Paiement Facture',
            'Achat Commerce',
            'Transfert International',
            'Retrait Espèces'
        ];

        return $destinations[array_rand($destinations)];
    }

    /**
     * Obtient un numéro de marchand aléatoire
     */
    private function getRandomMerchant(): string
    {
        $merchants = [
            '+221781234567', // Marchand 1
            '+221782345678', // Marchand 2
            '+221783456789', // Marchand 3
            '+221784567890', // Marchand 4
            '+221785678901'  // Marchand 5
        ];

        return $merchants[array_rand($merchants)];
    }

    /**
     * Obtient un numéro de mobile aléatoire pour recharges
     */
    private function getRandomMobileNumber(): string
    {
        $mobiles = [
            '+221771111111',
            '+221772222222',
            '+221773333333',
            '+221774444444',
            '+221775555555'
        ];

        return $mobiles[array_rand($mobiles)];
    }

    /**
     * Calcule le solde d'un compte
     */
    private function calculateBalance(string $numeroTelephone): float
    {
        $transactions = Transaction::where('expediteur', $numeroTelephone)
                                  ->orWhere('destinataire', $numeroTelephone)
                                  ->get();

        $balance = 0.0;

        foreach ($transactions as $transaction) {
            $type = strtolower($transaction->type_transaction);

            if (str_contains($type, 'dépôt')) {
                if ($transaction->destinataire === $numeroTelephone) {
                    $balance += $transaction->montant;
                }
            } elseif (str_contains($type, 'retrait')) {
                if ($transaction->expediteur === $numeroTelephone) {
                    $balance -= $transaction->montant;
                }
            } else {
                if ($transaction->destinataire === $numeroTelephone) {
                    $balance += $transaction->montant;
                } elseif ($transaction->expediteur === $numeroTelephone) {
                    $balance -= $transaction->montant;
                }
            }
        }

        return $balance;
    }
}
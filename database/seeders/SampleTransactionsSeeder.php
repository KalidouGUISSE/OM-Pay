<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Compte;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SampleTransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les comptes actifs
        $comptesActifs = Compte::where('statut', 'actif')->get();

        if ($comptesActifs->isEmpty()) {
            $this->command->error('Aucun compte actif trouvé. Veuillez exécuter les autres seeders d\'abord.');
            return;
        }

        $this->command->info('Création de transactions d\'exemple pour ' . $comptesActifs->count() . ' comptes actifs...');

        // Créer des dépôts initiaux pour chaque compte
        foreach ($comptesActifs as $compte) {
            // Dépôt initial entre 100,000 et 500,000 FCFA
            $montantDepot = rand(100000, 500000);
            $this->creerTransactionDepot($compte->numeroTelephone, $montantDepot);
        }

        // Créer des transferts entre comptes
        $this->creerTransfertsEntreComptes($comptesActifs);

        // Créer des retraits
        $this->creerRetraits($comptesActifs);

        $this->command->info('Transactions d\'exemple créées avec succès !');
    }

    /**
     * Créer un dépôt pour un compte
     */
    private function creerTransactionDepot(string $numeroTelephone, float $montant): void
    {
        // Pour les dépôts, l'expéditeur est null ou un numéro spécial
        Transaction::create([
            'id' => Str::uuid(),
            'type_transaction' => 'Dépôt',
            'expediteur' => '+221000000000', // Numéro spécial pour les dépôts
            'destinataire' => $numeroTelephone,
            'montant' => $montant,
            'date' => Carbon::now()->subDays(rand(1, 30)),
            'reference' => $this->genererReference(),
            'metadata' => [
                'type_operation' => 'depot',
                'source' => 'guichet_automatique',
                'derniereModification' => Carbon::now()->toISOString(),
                'version' => 1
            ]
        ]);
    }

    /**
     * Créer des transferts entre comptes
     */
    private function creerTransfertsEntreComptes($comptes): void
    {
        $nombresTransferts = min(20, $comptes->count() * 2); // 2 transferts par compte max

        for ($i = 0; $i < $nombresTransferts; $i++) {
            $expediteur = $comptes->random();
            $destinataire = $comptes->where('id', '!=', $expediteur->id)->random();

            $montant = rand(5000, 50000); // Transferts entre 5,000 et 50,000 FCFA

            Transaction::create([
                'id' => Str::uuid(),
                'type_transaction' => 'Transfert d\'argent',
                'expediteur' => $expediteur->numeroTelephone,
                'destinataire' => $destinataire->numeroTelephone,
                'montant' => $montant,
                'date' => Carbon::now()->subDays(rand(1, 15)),
                'reference' => $this->genererReference(),
                'metadata' => [
                    'type_operation' => 'transfert',
                    'motif' => $this->getMotifAleatoire(),
                    'derniereModification' => Carbon::now()->toISOString(),
                    'version' => 1
                ]
            ]);
        }
    }

    /**
     * Créer des retraits
     */
    private function creerRetraits($comptes): void
    {
        foreach ($comptes as $compte) {
            // 1 à 3 retraits par compte
            $nombreRetraits = rand(1, 3);

            for ($i = 0; $i < $nombreRetraits; $i++) {
                $montant = rand(10000, 100000); // Retraits entre 10,000 et 100,000 FCFA

                Transaction::create([
                    'id' => Str::uuid(),
                    'type_transaction' => 'Retrait',
                    'expediteur' => $compte->numeroTelephone,
                    'destinataire' => '+221000000001', // Numéro spécial pour les retraits
                    'montant' => $montant,
                    'date' => Carbon::now()->subDays(rand(1, 10)),
                    'reference' => $this->genererReference(),
                    'metadata' => [
                        'type_operation' => 'retrait',
                        'lieu_retrait' => $this->getLieuRetraitAleatoire(),
                        'derniereModification' => Carbon::now()->toISOString(),
                        'version' => 1
                    ]
                ]);
            }
        }
    }

    /**
     * Générer une référence unique
     */
    private function genererReference(): string
    {
        do {
            $reference = 'PP' . date('ym') . '.' . date('Y') . '.B' . strtoupper(Str::random(5));
        } while (Transaction::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Obtenir un motif de transfert aléatoire
     */
    private function getMotifAleatoire(): string
    {
        $motifs = [
            'Paiement de facture',
            'Achat en ligne',
            'Transfert familial',
            'Paiement de loyer',
            'Remboursement dette',
            'Épargne',
            'Achat de biens',
            'Paiement de services'
        ];

        return $motifs[array_rand($motifs)];
    }

    /**
     * Obtenir un lieu de retrait aléatoire
     */
    private function getLieuRetraitAleatoire(): string
    {
        $lieux = [
            'Dakar - Plateau',
            'Dakar - Yoff',
            'Dakar - Ouakam',
            'Dakar - Medina',
            'Saint-Louis - Centre',
            'Thiès - Marché',
            'Ziguinchor - Centre',
            'Kaolack - Marché',
            'Distributeur automatique'
        ];

        return $lieux[array_rand($lieux)];
    }
}

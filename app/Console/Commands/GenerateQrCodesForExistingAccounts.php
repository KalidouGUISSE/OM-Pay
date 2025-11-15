<?php

namespace App\Console\Commands;

use App\Models\Compte;
use Illuminate\Console\Command;

class GenerateQrCodesForExistingAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-qr-codes-for-existing-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère des QR codes pour tous les comptes existants qui n\'en ont pas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Recherche des comptes sans QR code...');

        $comptes = Compte::whereNull('code_qr')->get();

        if ($comptes->isEmpty()) {
            $this->info('✅ Tous les comptes ont déjà un QR code.');
            return;
        }

        $this->info("📝 Génération de QR codes pour {$comptes->count()} comptes...");

        $progressBar = $this->output->createProgressBar($comptes->count());
        $progressBar->start();

        foreach ($comptes as $compte) {
            try {
                $compte->update(['code_qr' => $compte->generateQrCode()]);
                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("❌ Erreur pour le compte {$compte->numeroCompte}: {$e->getMessage()}");
            }
        }

        $progressBar->finish();
        $this->newLine();

        $this->info('✅ Génération des QR codes terminée avec succès !');
        $this->info("📊 {$comptes->count()} comptes mis à jour.");
    }
}

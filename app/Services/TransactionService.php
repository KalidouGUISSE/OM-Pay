<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionService
{
    use ResponseTraits;

    protected TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function creerTransaction(Request $request)
    {
        try {
            $data = $request->all();

            // Générer une référence unique
            $data['reference'] = $this->genererReference();

            // Définir la date actuelle si non fournie
            $data['date'] = $data['date'] ?? Carbon::now();

            // Définir le type de transaction par défaut
            $data['type_transaction'] = $data['type_transaction'] ?? 'Transfert d\'argent';

            // Ajouter des métadonnées
            $data['metadata'] = [
                'derniereModification' => Carbon::now()->toISOString(),
                'version' => 1
            ];

            DB::beginTransaction();

            $transaction = $this->transactionRepository->create($data);

            DB::commit();

            return $this->successResponse('Transaction créée avec succès', [
                'id' => $transaction->id,
                'type de transaction' => $transaction->type_transaction,
                'Destinataire' => $transaction->destinataire,
                'Expediteur' => $transaction->expediteur,
                'montant' => $transaction->montant,
                'Date' => $transaction->date->toISOString(),
                'Reference' => $transaction->reference,
                'metadata' => $transaction->metadata
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Erreur lors de la création de la transaction', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTransaction(string $id)
    {
        $transaction = $this->transactionRepository->findById($id);

        if (!$transaction) {
            return $this->errorResponse('Transaction non trouvée', 'transaction_not_found', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse('Transaction récupérée', [
            'id' => $transaction->id,
            'type de transaction' => $transaction->type_transaction,
            'Destinataire' => $transaction->destinataire,
            'Expediteur' => $transaction->expediteur,
            'montant' => $transaction->montant,
            'Date' => $transaction->date->toISOString(),
            'Reference' => $transaction->reference,
            'metadata' => $transaction->metadata
        ]);
    }

    public function getTransactionsByExpediteur(string $expediteur)
    {
        $transactions = $this->transactionRepository->getByExpediteur($expediteur);

        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'type de transaction' => $transaction->type_transaction,
                'Destinataire' => $transaction->destinataire,
                'Expediteur' => $transaction->expediteur,
                'montant' => $transaction->montant,
                'Date' => $transaction->date->toISOString(),
                'Reference' => $transaction->reference,
                'metadata' => $transaction->metadata
            ];
        });

        return $this->successResponse('Transactions récupérées', $formattedTransactions->toArray());
    }

    public function getTransactionsByDestinataire(string $destinataire)
    {
        $transactions = $this->transactionRepository->getByDestinataire($destinataire);

        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'type de transaction' => $transaction->type_transaction,
                'Destinataire' => $transaction->destinataire,
                'Expediteur' => $transaction->expediteur,
                'montant' => $transaction->montant,
                'Date' => $transaction->date->toISOString(),
                'Reference' => $transaction->reference,
                'metadata' => $transaction->metadata
            ];
        });

        return $this->successResponse('Transactions récupérées', $formattedTransactions->toArray());
    }

    private function genererReference(): string
    {
        do {
            $reference = 'PP' . date('ym') . '.' . date('Y') . '.B' . strtoupper(Str::random(5));
        } while ($this->transactionRepository->findByReference($reference));

        return $reference;
    }
}
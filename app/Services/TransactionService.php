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

    public function getTransactionsForUser(Request $request)
    {
        $user = $request->user();

        // Extraire le numéro de téléphone depuis les abilities du token
        $numeroTelephone = null;
        $token = $user->currentAccessToken();
        if ($token) {
            foreach ($token->abilities ?? [] as $ability) {
                if (str_starts_with($ability, 'numero_telephone:')) {
                    $numeroTelephone = str_replace('numero_telephone:', '', $ability);
                    break;
                }
            }
        }

        if (!$numeroTelephone) {
            return $this->errorResponse('Numéro de téléphone non trouvé dans le token', 'numero_telephone_missing', Response::HTTP_BAD_REQUEST);
        }

        $transactions = $this->transactionRepository->getTransactionsForUser($numeroTelephone);

        $formattedTransactions = $transactions->map(function ($transaction) use ($numeroTelephone) {
            return [
                'id' => $transaction->id,
                'type de transfere' => $transaction->type_transaction,
                'Numero' => $transaction->expediteur === $numeroTelephone ? $transaction->destinataire : $transaction->expediteur,
                'montant' => $transaction->montant,
                'dateCreation' => $transaction->date->toISOString(),
                'metadata' => $transaction->metadata
            ];
        });

        return $this->successResponse('Transactions récupérées', $formattedTransactions->toArray());
    }

    public function getSolde(Request $request)
    {
        $user = $request->user();

        // Extraire le numéro de téléphone depuis les abilities du token
        $numeroTelephone = null;
        $token = $user->currentAccessToken();
        if ($token) {
            foreach ($token->abilities ?? [] as $ability) {
                if (str_starts_with($ability, 'numero_telephone:')) {
                    $numeroTelephone = str_replace('numero_telephone:', '', $ability);
                    break;
                }
            }
        }

        if (!$numeroTelephone) {
            return $this->errorResponse('Numéro de téléphone non trouvé dans le token', 'numero_telephone_missing', Response::HTTP_BAD_REQUEST);
        }

        $solde = $this->transactionRepository->calculateBalance($numeroTelephone);

        return $this->successResponse('Solde calculé avec succès', [
            'solde' => number_format($solde, 2, '.', ''),
            'devise' => 'FCFA',
            'numero_compte' => $numeroTelephone,
            'date_calculation' => now()->toISOString()
        ]);
    }

    public function getSoldeByNumero(string $numero)
    {
        $solde = $this->transactionRepository->calculateBalance($numero);

        return $this->successResponse('Solde calculé avec succès', [
            'solde' => number_format($solde, 2, '.', ''),
            'devise' => 'FCFA',
            'numero_compte' => $numero,
            'date_calculation' => now()->toISOString()
        ]);
    }

    public function getTransactionsByNumero(string $numero)
    {
        $transactions = $this->transactionRepository->getTransactionsForUser($numero);

        $formattedTransactions = $transactions->map(function ($transaction) use ($numero) {
            return [
                'id' => $transaction->id,
                'type de transfere' => $transaction->type_transaction,
                'Numero' => $transaction->expediteur === $numero ? $transaction->destinataire : $transaction->expediteur,
                'montant' => $transaction->montant,
                'dateCreation' => $transaction->date->toISOString(),
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
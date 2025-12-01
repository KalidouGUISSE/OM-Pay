<?php
namespace App\Repositories;

use App\Models\Transaction;
use App\Contracts\Interfaces\TransactionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function findById(string $id): ?Transaction
    {
        return Transaction::find($id);
    }

    public function findByReference(string $reference): ?Transaction
    {
        return Transaction::where('reference', $reference)->first();
    }

    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::all();
    }

    public function getByExpediteur(string $expediteur): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::where('expediteur', $expediteur)->get();
    }

    public function getByDestinataire(string $destinataire): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::where('destinataire', $destinataire)->get();
    }

    /**
     * Récupérer les transactions filtrées et paginées pour un utilisateur
     */
    public function getFilteredTransactionsForUser(
        string $numeroTelephone,
        array $filters = [],
        int $perPage = 15,
        string $sortBy = 'date',
        string $sortDirection = 'desc'
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {

        $query = Transaction::forUser($numeroTelephone);

        // Appliquer les filtres
        if (isset($filters['type']) && !empty($filters['type'])) {
            $query->ofType($filters['type']);
        }

        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $query->fromDate($filters['date_from']);
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $query->toDate($filters['date_to']);
        }

        if (isset($filters['date_range']) && is_array($filters['date_range']) && count($filters['date_range']) === 2) {
            $query->dateBetween($filters['date_range'][0], $filters['date_range'][1]);
        }

        if (isset($filters['direction']) && $filters['direction'] === 'incoming') {
            $query->incoming($numeroTelephone);
        } elseif (isset($filters['direction']) && $filters['direction'] === 'outgoing') {
            $query->outgoing($numeroTelephone);
        }

        // Appliquer le tri
        switch ($sortBy) {
            case 'date':
                $query->orderBy('date', $sortDirection);
                break;
            case 'amount':
            case 'montant':
                $query->orderBy('montant', $sortDirection);
                break;
            case 'type':
                $query->orderBy('type_transaction', $sortDirection)
                      ->orderBy('date', 'desc');
                break;
            default:
                $query->orderBy('date', 'desc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Méthode existante maintenue pour compatibilité
     */
    public function getTransactionsForUser(string $numeroTelephone): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::where('expediteur', $numeroTelephone)
                          ->orWhere('destinataire', $numeroTelephone)
                          ->orderBy('date', 'desc')
                          ->get();
    }

    public function calculateBalance(string $numeroTelephone): float
    {
        $transactions = $this->getTransactionsForUser($numeroTelephone);

        $balance = 0.0;

        foreach ($transactions as $transaction) {
            $type = strtolower($transaction->type_transaction);

            if (str_contains($type, 'dépôt')) {
                // Dépôt d'argent : toujours un crédit pour le destinataire
                if ($transaction->destinataire === $numeroTelephone) {
                    $balance += $transaction->montant;
                }
            } elseif (str_contains($type, 'retrait')) {
                // Retrait d'argent : toujours un débit pour l'expéditeur
                if ($transaction->expediteur === $numeroTelephone) {
                    $balance -= $transaction->montant;
                }
            } else {
                // Transferts, paiements, etc. : logique basée sur expéditeur/destinataire
                if ($transaction->destinataire === $numeroTelephone) {
                    // Réception : crédit
                    $balance += $transaction->montant;
                } elseif ($transaction->expediteur === $numeroTelephone) {
                    // Envoi : débit
                    $balance -= $transaction->montant;
                }
            }
        }

        return $balance;
    }
}
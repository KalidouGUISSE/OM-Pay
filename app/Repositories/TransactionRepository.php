<?php
namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepository
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
            if ($transaction->destinataire === $numeroTelephone) {
                // Si l'utilisateur est destinataire, on ajoute le montant
                $balance += $transaction->montant;
            } elseif ($transaction->expediteur === $numeroTelephone) {
                // Si l'utilisateur est expéditeur, on soustrait le montant
                $balance -= $transaction->montant;
            }
        }

        return $balance;
    }
}
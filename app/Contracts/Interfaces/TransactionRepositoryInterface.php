<?php

namespace App\Contracts\Interfaces;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
    /**
     * Crée une nouvelle transaction
     */
    public function create(array $data): Transaction;

    /**
     * Trouve une transaction par ID
     */
    public function findById(string $id): ?Transaction;

    /**
     * Trouve une transaction par référence
     */
    public function findByReference(string $reference): ?Transaction;

    /**
     * Récupère toutes les transactions
     */
    public function getAll(): Collection;

    /**
     * Récupère les transactions par expéditeur
     */
    public function getByExpediteur(string $expediteur): Collection;

    /**
     * Récupère les transactions par destinataire
     */
    public function getByDestinataire(string $destinataire): Collection;

    /**
     * Récupère les transactions filtrées et paginées pour un utilisateur
     */
    public function getFilteredTransactionsForUser(
        string $numeroTelephone,
        array $filters = [],
        int $perPage = 15,
        string $sortBy = 'date',
        string $sortDirection = 'desc'
    ): LengthAwarePaginator;

    /**
     * Récupère les transactions pour un utilisateur (méthode existante)
     */
    public function getTransactionsForUser(string $numeroTelephone): Collection;

    /**
     * Calcule le solde d'un compte
     */
    public function calculateBalance(string $numeroTelephone): float;
}
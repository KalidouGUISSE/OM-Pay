<?php

namespace App\Contracts\Interfaces;

use App\Models\Compte;
use Illuminate\Database\Eloquent\Collection;

interface CompteRepositoryInterface
{
    /**
     * Trouve un compte par numéro de téléphone
     */
    public function findByNumeroTelephone(string $numeroTelephone): ?Compte;

    /**
     * Récupère tous les comptes
     */
    public function getAll(): Collection;

    /**
     * Trouve un compte par ID
     */
    public function findById(string $id): ?Compte;
}
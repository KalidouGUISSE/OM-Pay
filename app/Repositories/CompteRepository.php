<?php
namespace App\Repositories;

use App\Models\Compte;
use App\Contracts\Interfaces\CompteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CompteRepository implements CompteRepositoryInterface
{

    // public function findByTelephone(string $numeroTelephone): ?Compte
    // {
    //     return Compte::where('numeroTelephone', $numeroTelephone)->first();
    // }

    public function findByNumeroTelephone(string $numeroTelephone): ?Compte
    {
        return Compte::where('numeroTelephone', $numeroTelephone)->first();
    }

        public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Compte::all();
    }

    public function findById(string $id): ?Compte
    {
        return Compte::find($id);
    }

    public function findByMerchantCode(string $merchantCode): ?Compte
    {
        return Compte::where('numeroCompte', $merchantCode)
                    ->where('type', 'marchand')
                    ->where('statut', 'actif')
                    ->first();
    }
}

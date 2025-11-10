<?php
namespace App\Repositories;

use App\Models\Compte;

class CompteRepository
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
}

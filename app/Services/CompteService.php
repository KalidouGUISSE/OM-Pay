<?php

namespace App\Services ;
use Exception;
use App\Models\Client;
use App\Models\Compte;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
class CompteService {

    public  function makeFilter($request , $query){

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        return $query ;
    }


    public function makeSearch($request , $query){
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numeroCompte', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('nom', 'like', "%{$search}%")
                                ->orWhere('prenom', 'like', "%{$search}%")
                                ->orWhere('telephone', 'like', "%{$search}%");
                });
            });
        }
        return $query; 
    }

    // public function creerCompteClient(array $data){
    //     try {
    //         return DB::transaction(function () use ($data) {
    //             $password = "pwd" . "-" . mt_rand(100000, 99999999999);
    //             $remember_token = "token" . "--" . mt_rand(100000, 99999999999);

    //             $client = Client::create([
    //                 "nom" => $data['nom'],
    //                 "prenom" => $data['prenom'],
    //                 "cni" => $data['cni'],
    //                 "email" => $data['email'],
    //                 "telephone" => $data['telephone'],
    //                 "date_naissance" => $data['date_naissance'] ?? null,
    //                 "adresse" => $data['adresse'] ?? null,
    //                 "genre" => $data['genre'] ?? null,
    //                 "password" => $password,
    //                 "remember_token" => $remember_token,
    //                 "role" => "client"
    //             ]);

    //             $numeroCompte = "NCMTP" . "--" . mt_rand(1220000000, 99999999999999);

    //             $compte = Compte::create([
    //                 "numeroCompte" => $numeroCompte,
    //                 "type" => $data['type'],
    //                 "devise" => $data['devise'],
    //                 "statut" => $data['statut'] ?? 'actif',
    //                 "client_id" => $client->id
    //             ]);

    //             return ["compte" => $compte, "client" => $client];
    //         });
    //     } catch (Exception $e) {
    //         // Vous pouvez gérer ici l'exception ou la remonter au contrôleur
    //         throw new Exception("Erreur lors de la création du compte : " . $e->getMessage());
    //     }
    // }
    
}
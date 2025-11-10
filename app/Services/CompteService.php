<?php

namespace App\Services ;

use App\Repositories\CompteRepository;
use Illuminate\Http\Request;
// use App\Traits\ApiResponse;
use App\Traits\ResponseTraits;


use Exception;
use App\Models\Client;
use App\Models\Compte;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
class CompteService {

    // use ApiResponse;
    use ResponseTraits;


    protected CompteRepository $compteRepository;

    public function __construct(CompteRepository $compteRepository)
    {
        $this->compteRepository = $compteRepository;
    }

    public function getComptesForUser(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'client') {
            $token = $user->currentAccessToken();
            $compteId = $this->extractCompteIdFromToken($token->abilities ?? []);

            if (!$compteId) {
                return $this->errorResponse('ID du compte non trouvé dans le token', 'compte_id_missing', 400);
            }

            $compte = $this->compteRepository->findById($compteId);

            if (!$compte || $compte->id_client !== $user->id) {
                return $this->errorResponse('Compte non trouvé ou accès non autorisé', 'compte_not_found', 404);
            }

            return $this->successResponse('Compte récupéré', [$compte]);
        }

        $comptes = $this->compteRepository->getAll();
        return $this->successResponse('Comptes récupérés', $comptes->toArray());
    }


    private function extractCompteIdFromToken(array $abilities): ?string
    {
        foreach ($abilities as $ability) {
            if (str_starts_with($ability, 'compte_id:')) {
                return str_replace('compte_id:', '', $ability);
            }
        }
        return null;
    }




















    // public  function makeFilter($request , $query){

    //     if ($request->filled('type')) {
    //         $query->where('type', $request->type);
    //     }

    //     // Filtre par statut
    //     if ($request->filled('statut')) {
    //         $query->where('statut', $request->statut);
    //     }
    //     return $query ;
    // }


    // public function makeSearch($request , $query){
    //     if ($request->filled('search')) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->where('numeroCompte', 'like', "%{$search}%")
    //               ->orWhereHas('client', function ($clientQuery) use ($search) {
    //                   $clientQuery->where('nom', 'like', "%{$search}%")
    //                             ->orWhere('prenom', 'like', "%{$search}%")
    //                             ->orWhere('telephone', 'like', "%{$search}%");
    //             });
    //         });
    //     }
    //     return $query; 
    // }

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
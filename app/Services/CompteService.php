<?php

namespace App\Services ;

use App\Repositories\CompteRepository;
use Illuminate\Http\Request;
// use App\Traits\ApiResponse;
use App\Traits\ResponseTraits;


use Exception;
use App\Models\User;
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
            // Pour les clients, retourner tous leurs comptes
            $query = $user->comptes();

            // Appliquer les filtres
            $query = $this->makeFilter($request, $query);
            $query = $this->makeSearch($request, $query);

            $comptes = $query->get();

            if ($comptes->isEmpty()) {
                return $this->errorResponse('Aucun compte trouvé pour cet utilisateur', 'no_accounts_found', 404);
            }

            return $this->successResponse('Comptes récupérés', $comptes->toArray());
        }

        $query = Compte::query();
        $query = $this->makeFilter($request, $query);
        $query = $this->makeSearch($request, $query);

        $comptes = $query->get();
        return $this->successResponse('Comptes récupérés', $comptes->toArray());
    }

    public function makeFilter($request, $query)
    {
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        return $query;
    }

    public function makeSearch($request, $query)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numeroCompte', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('nom', 'like', "%{$search}%")
                                ->orWhere('prenom', 'like', "%{$search}%")
                                ->orWhere('numeroTelephone', 'like', "%{$search}%");
                });
            });
        }
        return $query;
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































    public function creerCompteClient(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                // Utiliser User au lieu de Client
                $user = User::create([
                    "nom" => $data['nom'],
                    "prenom" => $data['prenom'],
                    "numeroTelephone" => $data['telephone'],
                    "role" => "client"
                ]);

                $numeroCompte = "NCMTP" . mt_rand(1000000000, 99999999999999);

                $compte = Compte::create([
                    "numeroCompte" => $numeroCompte,
                    "numeroTelephone" => $data['telephone'],
                    "type" => $data['type'],
                    "dateCreation" => now()->toDateString(),
                    "statut" => $data['statut'] ?? 'actif',
                    "id_client" => $user->id
                ]);

                return ["compte" => $compte, "user" => $user];
            });
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création du compte : " . $e->getMessage());
        }
    }

    public function createCompte(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Chercher l'utilisateur par numéro de carte d'identité
            $existingUser = User::where('numero_carte_identite', $data['numero_carte_identite'])->first();
            $isNewUser = false;

            if (!$existingUser) {
                // Vérifier que les informations personnelles sont fournies
                if (empty($data['nom']) || empty($data['prenom'])) {
                    throw new Exception('Informations personnelles requises pour créer un nouvel utilisateur.');
                }

                // Créer un nouvel utilisateur
                $user = User::create([
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom'],
                    'email' => $data['email'] ?? null,
                    'role' => 'client',
                    'numero_carte_identite' => $data['numero_carte_identite'],
                ]);
                $isNewUser = true;
            } else {
                $user = $existingUser;
            }

            // Générer numéro de compte unique
            do {
                $numeroCompte = 'NCMTP' . mt_rand(1000000000, 99999999999999);
            } while (Compte::where('numeroCompte', $numeroCompte)->exists());

            // Créer le compte
            $compteData = [
                'id_client' => $user->id,
                'numeroCompte' => $numeroCompte,
                'numeroTelephone' => $data['numeroTelephone'],
                'type' => $data['type'],
                'statut' => $data['statut'] ?? 'actif',
                'codePing' => $data['codePing'] ?? null,
                'codePingPlain' => $data['codePing'] ?? null,
            ];

            // Ajouter dateCreation seulement si fournie
            if (!empty($data['dateCreation'])) {
                $compteData['dateCreation'] = $data['dateCreation'];
            }

            $compte = Compte::create($compteData);

            return [
                'compte' => $compte,
                'user' => $user,
                'is_new_user' => $isNewUser
            ];
        });
    }

    public function addCompteForUser(array $data, User $user)
    {
        return DB::transaction(function () use ($data, $user) {
            // Générer numéro de compte unique
            do {
                $numeroCompte = 'NCMTP' . mt_rand(1000000000, 99999999999999);
            } while (Compte::where('numeroCompte', $numeroCompte)->exists());

            // Créer le compte pour l'utilisateur existant
            $compte = Compte::create([
                'id_client' => $user->id,
                'numeroCompte' => $numeroCompte,
                'numeroTelephone' => $data['numeroTelephone'],
                'type' => $data['type'],
                'statut' => $data['statut'] ?? 'actif',
                'codePing' => $data['codePing'] ?? null,
                'codePingPlain' => $data['codePing'] ?? null,
            ]);

            return [
                'compte' => $compte,
                'user' => $user,
            ];
        });
    }

}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Compte;
use App\Services\CompteService;


class CompteController extends Controller
{
    
    use ResponseTraits;
    // use CompteService ;

    private CompteService $compteService;

    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
    }

        /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Si c'est un client, retourner seulement le compte avec lequel il s'est connecté
        if ($user->role === 'client') {
            // Pour l'instant, récupérer le premier compte actif du client
            // TODO: Dans une vraie implémentation, stocker l'ID du compte dans le token
            $compte = Compte::where('id_client', $user->id)
                           ->where('statut', 'actif')
                           ->first();

            if (!$compte) {
                return $this->errorResponse('Aucun compte actif trouvé pour cet utilisateur', 'compte_not_found', 404);
            }

            return $this->successResponse('compte recuperer', [$compte]);
        }

        // Si c'est un admin, retourner tous les comptes
        $comptes = Compte::all();
        return $this->successResponse('comptes recuperer', $comptes->toArray());
    }

        /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();

        $compte = Compte::find($id);
        if (!$compte) {
            return $this->errorResponse("compte non trouve", 'compte nom trouver', Response::HTTP_NOT_FOUND);
        }

        // Si c'est un client, vérifier qu'il accède à son propre compte
        if ($user->role === 'client' && $compte->id_client !== $user->id) {
            return $this->errorResponse("Accès non autorisé à ce compte", 'unauthorized', Response::HTTP_FORBIDDEN);
        }

        return $this->successResponse('compte recuperer', [$compte]);
    }
}

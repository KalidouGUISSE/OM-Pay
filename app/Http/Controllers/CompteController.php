<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Compte;
use App\Services\CompteService;
use App\Http\Requests\CompteIndexRequest;

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
    public function index(CompteIndexRequest $request)
    {
        return $this->compteService->getComptesForUser($request);
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

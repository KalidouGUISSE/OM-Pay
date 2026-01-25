<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Compte;
use App\Services\CompteService;
use App\Http\Requests\CompteIndexRequest;
use App\Http\Requests\CreateCompteRequest;
use App\Http\Requests\AddCompteRequest;
use App\Models\User;

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
            return $this->errorResponse("Compte non trouvé", 'compte_not_found', Response::HTTP_NOT_FOUND);
        }

        // Si c'est un client, vérifier qu'il accède à son propre compte
        if ($user->role === 'client' && $compte->id_client !== $user->id) {
            return $this->errorResponse("Accès non autorisé à ce compte", 'unauthorized', Response::HTTP_FORBIDDEN);
        }

        return $this->successResponse('Compte récupéré', [$compte]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCompteRequest $request)
    {
        $data = $request->validated();
        $result = $this->compteService->createCompte($data);

        $message = $result['is_new_user']
            ? 'Compte et utilisateur créés avec succès'
            : 'Compte créé avec succès pour l\'utilisateur existant';

        return $this->successResponse($message, [
            'compte' => $result['compte'],
            'user' => $result['user'],
            'is_new_user' => $result['is_new_user']
        ], Response::HTTP_CREATED);
    }

    /**
     * Add an additional account for the authenticated user.
     */
    public function add(AddCompteRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $result = $this->compteService->addCompteForUser($data, $user);

        return $this->successResponse('Compte supplémentaire créé avec succès', [
            'compte' => $result['compte'],
            'user' => $result['user'],
        ], Response::HTTP_CREATED);
    }
}

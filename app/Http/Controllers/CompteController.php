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

        $comptes = Compte::all();

        $query = Compte::with(['client']);

        $query = $this->compteService->makeFilter($request, $query);

        $query = $this->compteService->makeSearch($request, $query);
        return [$comptes];


        // // Pagination
        // $perPage = $request->get('limit', 10);
        // $comptes = $query->paginate($perPage);

        // return $this->paginatedResponse(
        //     [$comptes],
        //     $comptes->currentPage(),
        //     $comptes->lastPage(),
        //     $comptes->total(),
        //     $comptes->perPage(),
        //     $comptes->hasMorePages(),
        //     $comptes->currentPage() > 1,
        //     [
        //         'self' => $comptes->url($comptes->currentPage()),
        //         'next' => $comptes->nextPageUrl(),
        //         'first' => $comptes->url(1),
        //         'last' => $comptes->url($comptes->lastPage()),
        //         'previous' => $comptes->previousPageUrl(),
        //     ]
        // );
    }

        /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $compte = Compte::find($id);
        if (!$compte) {
            return $this->errorResponse("compte non trouve", 'compte nom trouver', Response::HTTP_NOT_FOUND);
        }
        return $this->successResponse('compte recuperer', [$compte]);
    }
}

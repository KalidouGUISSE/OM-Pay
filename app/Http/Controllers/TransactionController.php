<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use Symfony\Component\HttpFoundation\Response;
use App\Services\TransactionService;
use App\Http\Requests\TransactionRequest;

class TransactionController extends Controller
{
    use ResponseTraits;

    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Créer une nouvelle transaction
     */
    public function store(TransactionRequest $request)
    {
        return $this->transactionService->creerTransaction($request);
    }

    /**
     * Récupérer une transaction par ID
     */
    public function show(Request $request, string $id)
    {
        return $this->transactionService->getTransaction($id);
    }

    /**
     * Récupérer les transactions par expéditeur
     */
    public function getByExpediteur(Request $request, string $expediteur)
    {
        // Validation du numéro de téléphone
        $request->validate([
            'expediteur' => 'required|string|regex:/^\+221[0-9]{9}$/',
        ]);

        return $this->transactionService->getTransactionsByExpediteur($expediteur);
    }

    /**
     * Récupérer les transactions par destinataire
     */
    public function getByDestinataire(Request $request, string $destinataire)
    {
        // Validation du numéro de téléphone
        $request->validate([
            'destinataire' => 'required|string|regex:/^\+221[0-9]{9}$/',
        ]);

        return $this->transactionService->getTransactionsByDestinataire($destinataire);
    }

    /**
     * Récupérer toutes les transactions de l'utilisateur connecté
     */
    public function index(Request $request)
    {
        return $this->transactionService->getTransactionsForUser($request);
    }
}

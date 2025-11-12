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
     * Récupérer toutes les transactions d'un compte avec filtrage et pagination
     *
     * Paramètres de requête supportés :
     * - type: Filtrer par type (Dépôt, Retrait, Transfert d'argent)
     * - date_from: Date de début (YYYY-MM-DD)
     * - date_to: Date de fin (YYYY-MM-DD)
     * - direction: incoming/outgoing (transactions reçues/envoyées)
     * - per_page: Nombre d'éléments par page (1-100, défaut: 15)
     * - sort_by: Tri par (date, amount, type, défaut: date)
     * - sort_direction: Direction du tri (asc, desc, défaut: desc)
     */
    public function index(Request $request, string $numero)
    {
        // Validation du numéro de téléphone passé en paramètre URL
        if (!preg_match('/^\+221[0-9]{9}$/', $numero)) {
            return $this->errorResponse('Format de numéro de téléphone invalide', 'invalid_phone_format', 400);
        }

        return $this->transactionService->getTransactionsForUserByNumero($request, $numero);
    }

    /**
     * Récupérer le solde du compte de l'utilisateur connecté
     */
    public function getSolde(Request $request)
    {
        return $this->transactionService->getSolde($request);
    }

    /**
     * Récupérer le solde d'un compte par numéro
     */
    public function getSoldeByNumero(Request $request, string $numero)
    {
        return $this->transactionService->getSoldeByNumero($numero);
    }

    /**
     * Récupérer les transactions d'un compte par numéro
     */
    public function getTransactionsByNumero(Request $request, string $numero)
    {
        return $this->transactionService->getTransactionsByNumero($numero);
    }
}

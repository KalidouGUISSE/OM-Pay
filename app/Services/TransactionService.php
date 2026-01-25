<?php

namespace App\Services;

use App\Contracts\Interfaces\TransactionRepositoryInterface;
use App\Contracts\Interfaces\CompteRepositoryInterface;
use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Rules\PhoneTransferValidator;
use App\Rules\MerchantTransferValidator;

class TransactionService
{
    use ResponseTraits;

    protected TransactionRepositoryInterface $transactionRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Valide les données de transfert selon le type spécifié
     */
    private function validateTransferData(array $data): mixed
    {
        // Récupérer le type de transfert depuis les données
        $typeTransfert = $data['type_transfert'] ?? 'telephone'; // Par défaut téléphone

        $validator = null;

        switch ($typeTransfert) {
            case 'telephone':
                // Validation pour transfert par numéro de téléphone
                $validator = Validator::make($data, [
                    'numero du destinataire' => ['required', 'string', new PhoneTransferValidator($data['expediteur'] ?? null)],
                    'montant' => 'required|numeric|min:0.01',
                    'expediteur' => 'required|string|regex:/^\+221[0-9]{9}$/',
                ], [
                    'numero du destinataire.required' => 'Le numéro du destinataire est requis pour ce type de transfert.',
                    'montant.required' => 'Le montant est requis.',
                    'montant.numeric' => 'Le montant doit être un nombre.',
                    'montant.min' => 'Le montant doit être supérieur à 0.',
                    'expediteur.required' => 'L\'expéditeur est requis.',
                    'expediteur.regex' => 'Le numéro de l\'expéditeur doit être un numéro sénégalais valide.',
                ]);

                // Si la validation passe, définir le destinataire
                if ($validator->passes()) {
                    $data['destinataire'] = $data['numero du destinataire'];
                }
                break;

            case 'marchand':
                // Validation pour transfert par code marchand
                $validator = Validator::make($data, [
                    'code_marchand' => ['required', 'string', new MerchantTransferValidator()],
                    'montant' => 'required|numeric|min:0.01',
                    'expediteur' => 'required|string|regex:/^\+221[0-9]{9}$/',
                ], [
                    'code_marchand.required' => 'Le code marchand est requis pour ce type de transfert.',
                    'montant.required' => 'Le montant est requis.',
                    'montant.numeric' => 'Le montant doit être un nombre.',
                    'montant.min' => 'Le montant doit être supérieur à 0.',
                    'expediteur.required' => 'L\'expéditeur est requis.',
                    'expediteur.regex' => 'Le numéro de l\'expéditeur doit être un numéro sénégalais valide.',
                ]);

                // Si la validation passe, récupérer le numéro de téléphone du marchand
                if ($validator->passes()) {
                    $compteRepository = app(CompteRepositoryInterface::class);
                    $compteMarchand = $compteRepository->findByMerchantCode($data['code_marchand']);
                    if ($compteMarchand) {
                        $data['destinataire'] = $compteMarchand->numeroTelephone;
                    }
                }
                break;

            default:
                return $this->errorResponse('Type de transfert non supporté', 'unsupported_transfer_type', Response::HTTP_BAD_REQUEST);
        }

        if ($validator && $validator->fails()) {
            return $this->errorResponse(
                'Données de transfert invalides: ' . $validator->errors()->first(),
                'validation_failed',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return true; // Validation réussie
    }

    public function creerTransaction(Request $request, string $expediteur = null)
    {
        try {
            $data = $request->all();

            // Si un expéditeur est fourni (depuis l'URL), l'utiliser
            if ($expediteur) {
                $data['expediteur'] = $expediteur;
            }

            // Valider les données selon le type de transfert
            $validationResult = $this->validateTransferData($data);
            if ($validationResult !== true) {
                return $validationResult; // Retourner l'erreur de validation
            }

            // Générer une référence unique
            $data['reference'] = $this->genererReference();

            // Définir la date actuelle si non fournie
            $data['date'] = $data['date'] ?? Carbon::now();

            // Définir le type de transaction par défaut
            $data['type_transaction'] = $data['type_transaction'] ?? 'Transfert d\'argent';

            // Ajouter des métadonnées
            $data['metadata'] = [
                'derniereModification' => Carbon::now()->toISOString(),
                'version' => 1
            ];

            // Vérifier le solde de l'expéditeur avant la transaction
            $soldeExpediteur = $this->transactionRepository->calculateBalance($data['expediteur']);
            if ($soldeExpediteur < $data['montant']) {
                return $this->errorResponse(
                    'Solde insuffisant. Solde actuel: ' . number_format($soldeExpediteur, 2) . ' FCFA, Montant demandé: ' . number_format($data['montant'], 2) . ' FCFA',
                    'insufficient_balance',
                    Response::HTTP_BAD_REQUEST
                );
            }

            DB::beginTransaction();

            $transaction = $this->transactionRepository->create($data);

            DB::commit();

            return $this->successResponse('Transaction créée avec succès', [
                'id' => $transaction->id,
                'type de transaction' => $transaction->type_transaction,
                'Destinataire' => $transaction->destinataire,
                'Expediteur' => $transaction->expediteur,
                'montant' => $transaction->montant,
                'Date' => $transaction->date->toISOString(),
                'Reference' => $transaction->reference,
                'metadata' => $transaction->metadata
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Erreur lors de la création de la transaction', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTransaction(string $id)
    {
        $transaction = $this->transactionRepository->findById($id);

        if (!$transaction) {
            return $this->errorResponse('Transaction non trouvée', 'transaction_not_found', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse('Transaction récupérée', [
            'id' => $transaction->id,
            'type de transaction' => $transaction->type_transaction,
            'Destinataire' => $transaction->destinataire,
            'Expediteur' => $transaction->expediteur,
            'montant' => $transaction->montant,
            'Date' => $transaction->date->toISOString(),
            'Reference' => $transaction->reference,
            'metadata' => $transaction->metadata
        ]);
    }

    public function getTransactionsByExpediteur(string $expediteur)
    {
        $transactions = $this->transactionRepository->getByExpediteur($expediteur);

        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'type de transaction' => $transaction->type_transaction,
                'Destinataire' => $transaction->destinataire,
                'Expediteur' => $transaction->expediteur,
                'montant' => $transaction->montant,
                'Date' => $transaction->date->toISOString(),
                'Reference' => $transaction->reference,
                'metadata' => $transaction->metadata
            ];
        });

        return $this->successResponse('Transactions récupérées', $formattedTransactions->toArray());
    }

    public function getTransactionsByDestinataire(string $destinataire)
    {
        $transactions = $this->transactionRepository->getByDestinataire($destinataire);

        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'type de transaction' => $transaction->type_transaction,
                'Destinataire' => $transaction->destinataire,
                'Expediteur' => $transaction->expediteur,
                'montant' => $transaction->montant,
                'Date' => $transaction->date->toISOString(),
                'Reference' => $transaction->reference,
                'metadata' => $transaction->metadata
            ];
        });

        return $this->successResponse('Transactions récupérées', $formattedTransactions->toArray());
    }

    /**
     * Récupérer toutes les transactions de l'utilisateur connecté avec filtrage et pagination
     */
    public function getTransactionsForUser(Request $request)
    {
        $user = $request->user();

        // Récupérer tous les numéros de téléphone des comptes de l'utilisateur
        $numerosTelephones = $user->comptes->pluck('numeroTelephone')->toArray();

        if (empty($numerosTelephones)) {
            return $this->errorResponse('Aucun compte trouvé pour cet utilisateur', 'no_accounts_found', Response::HTTP_BAD_REQUEST);
        }

        // Utiliser le premier numéro de téléphone (les utilisateurs ont généralement un compte)
        $numeroTelephone = $numerosTelephones[0];

        // Récupérer les paramètres de filtrage
        $filters = [
            'type' => $request->query('type'), // Dépôt, Retrait, Transfert d'argent
            'date_from' => $request->query('date_from'), // YYYY-MM-DD
            'date_to' => $request->query('date_to'), // YYYY-MM-DD
            'direction' => $request->query('direction'), // incoming, outgoing
        ];

        // Supprimer les valeurs nulles/vides
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        // Paramètres de tri et pagination
        $perPage = $request->query('per_page', 15);
        $sortBy = $request->query('sort_by', 'date'); // date, amount, type
        $sortDirection = $request->query('sort_direction', 'desc'); // asc, desc

        // Validation des paramètres
        if ($perPage < 1 || $perPage > 100) {
            $perPage = 15;
        }

        $validSortFields = ['date', 'amount', 'montant', 'type'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'date';
        }

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $transactions = $this->transactionRepository->getFilteredTransactionsForUser(
            $numeroTelephone,
            $filters,
            $perPage,
            $sortBy,
            $sortDirection
        );

        $formattedTransactions = collect($transactions->items())->map(function ($transaction) use ($numeroTelephone) {
            return [
                'id' => $transaction->id,
                'type de transfere' => $transaction->type_transaction,
                'Numero' => $transaction->expediteur === $numeroTelephone ? $transaction->destinataire : $transaction->expediteur,
                'montant' => $transaction->montant,
                'dateCreation' => $transaction->date->toISOString(),
                'metadata' => $transaction->metadata
            ];
        });

        return $this->successResponse('Transactions récupérées', [
            'transactions' => $formattedTransactions,
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'from' => $transactions->firstItem(),
                'to' => $transactions->lastItem(),
            ],
            'filters_applied' => $filters,
            'sort' => [
                'by' => $sortBy,
                'direction' => $sortDirection
            ]
        ]);
    }

    public function getSolde(Request $request)
    {
        $user = $request->user();

        // Récupérer tous les numéros de téléphone des comptes de l'utilisateur
        $numerosTelephones = $user->comptes->pluck('numeroTelephone')->toArray();

        if (empty($numerosTelephones)) {
            return $this->errorResponse('Aucun compte trouvé pour cet utilisateur', 'no_accounts_found', Response::HTTP_BAD_REQUEST);
        }

        // Utiliser le premier numéro de téléphone
        $numeroTelephone = $numerosTelephones[0];

        $solde = $this->transactionRepository->calculateBalance($numeroTelephone);

        return $this->successResponse('Solde calculé avec succès', [
            'solde' => number_format($solde, 2, '.', ''),
            'devise' => 'FCFA',
            'numero_compte' => $numeroTelephone,
            'date_calculation' => now()->toISOString()
        ]);
    }

    public function getSoldeByNumero(string $numero)
    {
        // Validation du numéro de téléphone sénégalais
        if (!preg_match('/^\+221[0-9]{9}$/', $numero)) {
            return $this->errorResponse('Numéro de téléphone invalide. Seuls les numéros sénégalais (+221...) sont acceptés.', 'invalid_phone_number', Response::HTTP_BAD_REQUEST);
        }

        $solde = $this->transactionRepository->calculateBalance($numero);

        return $this->successResponse('Solde calculé avec succès', [
            'solde' => number_format($solde, 2, '.', ''),
            'devise' => 'FCFA',
            'numero_compte' => $numero,
            'date_calculation' => now()->toISOString()
        ]);
    }

    /**
     * Récupérer toutes les transactions d'un compte spécifique avec filtrage et pagination
     */
    public function getTransactionsForUserByNumero(Request $request, string $numeroTelephone)
    {
        // Récupérer les paramètres de filtrage
        $filters = [
            'type' => $request->query('type'), // Dépôt, Retrait, Transfert d'argent
            'date_from' => $request->query('date_from'), // YYYY-MM-DD
            'date_to' => $request->query('date_to'), // YYYY-MM-DD
            'direction' => $request->query('direction'), // incoming, outgoing
        ];

        // Supprimer les valeurs nulles/vides
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        // Paramètres de tri et pagination
        $perPage = $request->query('per_page', 15);
        $sortBy = $request->query('sort_by', 'date'); // date, amount, type
        $sortDirection = $request->query('sort_direction', 'desc'); // asc, desc

        // Validation des paramètres
        if ($perPage < 1 || $perPage > 100) {
            $perPage = 15;
        }

        $validSortFields = ['date', 'amount', 'montant', 'type'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'date';
        }

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $transactions = $this->transactionRepository->getFilteredTransactionsForUser(
            $numeroTelephone,
            $filters,
            $perPage,
            $sortBy,
            $sortDirection
        );

        $formattedTransactions = collect($transactions->items())->map(function ($transaction) use ($numeroTelephone) {
            return [
                'id' => $transaction->id,
                'type de transfere' => $transaction->type_transaction,
                'Numero' => $transaction->expediteur === $numeroTelephone ? $transaction->destinataire : $transaction->expediteur,
                'montant' => $transaction->montant,
                'dateCreation' => $transaction->date->toISOString(),
                'metadata' => $transaction->metadata
            ];
        });

        return $this->successResponse('Transactions récupérées', [
            'transactions' => $formattedTransactions,
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'from' => $transactions->firstItem(),
                'to' => $transactions->lastItem(),
            ],
            'filters_applied' => $filters,
            'sort' => [
                'by' => $sortBy,
                'direction' => $sortDirection
            ]
        ]);
    }

    public function getTransactionsByNumero(string $numero)
    {
        $transactions = $this->transactionRepository->getTransactionsForUser($numero);

        $formattedTransactions = $transactions->map(function ($transaction) use ($numero) {
            return [
                'id' => $transaction->id,
                'type de transfere' => $transaction->type_transaction,
                'Numero' => $transaction->expediteur === $numero ? $transaction->destinataire : $transaction->expediteur,
                'montant' => $transaction->montant,
                'dateCreation' => $transaction->date->toISOString(),
                'metadata' => $transaction->metadata
            ];
        });

        return $this->successResponse('Transactions récupérées', $formattedTransactions->toArray());
    }

    private function genererReference(): string
    {
        do {
            $reference = 'PP' . date('ym') . '.' . date('Y') . '.B' . strtoupper(Str::random(5));
        } while ($this->transactionRepository->findByReference($reference));

        return $reference;
    }
}
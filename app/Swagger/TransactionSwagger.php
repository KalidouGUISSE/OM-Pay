<?php

namespace App\Swagger;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="Gestion des transactions"
 * )
 */
class TransactionSwagger
{
    /**
     * @OA\Post(
     *     path="/api/v1/transactions",
     *     summary="Créer une nouvelle transaction",
     *     description="Crée une nouvelle transaction avec les informations fournies.",
     *     tags={"Transactions"},
     *     security={{"sanctum": {}}},
     * @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numero du destinataire", "montant", "type_transaction"},
     *             @OA\Property(property="numero du destinataire", type="string", example="767654567", description="Numéro du destinataire (9 chiffres, doit exister en base)"),
     *             @OA\Property(property="montant", type="number", format="float", example=35000, description="Montant de la transaction (doit être positif)"),
     *             @OA\Property(property="type_transaction", type="string", enum={"transfert", "Transfert d'argent"}, example="Transfert d'argent", description="Type de transaction (obligatoire)"),
     *             @OA\Property(property="date", type="string", format="date-time", example="2023-03-15T00:00:00Z", description="Date de la transaction (optionnel)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction créée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transaction créée avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="type de transaction", type="string", example="Transfert d'argent"),
     *                 @OA\Property(property="Destinataire", type="string", example="765463726"),
     *                 @OA\Property(property="Expediteur", type="string", example="776458909"),
     *                 @OA\Property(property="montant", type="number", format="float", example=500),
     *                 @OA\Property(property="Date", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                 @OA\Property(property="Reference", type="string", example="PP250723.2020.B66700"),
     *                 @OA\Property(
     *                     property="metadata",
     *                     type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time", example="2023-06-10T14:30:00Z"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Données invalides"),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
     */
    public function store() {}

    /**
     * @OA\Get(
     *     path="/api/v1/transactions/{id}",
     *     summary="Récupérer une transaction par ID",
     *     description="Retourne les détails d'une transaction spécifique.",
     *     tags={"Transactions"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="ID de la transaction"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction récupérée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transaction récupérée"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="type de transaction", type="string", example="Transfert d'argent"),
     *                 @OA\Property(property="Destinataire", type="string", example="765463726"),
     *                 @OA\Property(property="Expediteur", type="string", example="776458909"),
     *                 @OA\Property(property="montant", type="number", format="float", example=500),
     *                 @OA\Property(property="Date", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                 @OA\Property(property="Reference", type="string", example="PP250723.2020.B66700"),
     *                 @OA\Property(
     *                     property="metadata",
     *                     type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time", example="2023-06-10T14:30:00Z"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Transaction non trouvée"),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
     */
    public function show() {}

    /**
     * @OA\Get(
     *     path="/api/v1/transactions/expediteur/{expediteur}",
     *     summary="Récupérer les transactions par expéditeur",
     *     description="Retourne toutes les transactions d'un expéditeur spécifique.",
     *     tags={"Transactions"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="expediteur",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Numéro de téléphone de l'expéditeur"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transactions récupérées",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transactions récupérées"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="type de transaction", type="string", example="Transfert d'argent"),
     *                     @OA\Property(property="Destinataire", type="string", example="765463726"),
     *                     @OA\Property(property="Expediteur", type="string", example="776458909"),
     *                     @OA\Property(property="montant", type="number", format="float", example=500),
     *                     @OA\Property(property="Date", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                     @OA\Property(property="Reference", type="string", example="PP250723.2020.B66700"),
     *                     @OA\Property(
     *                         property="metadata",
     *                         type="object",
     *                         @OA\Property(property="derniereModification", type="string", format="date-time", example="2023-06-10T14:30:00Z"),
     *                         @OA\Property(property="version", type="integer", example=1)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Numéro invalide"),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
     */
    public function getByExpediteur() {}

    /**
     * @OA\Get(
     *     path="/api/v1/transactions/destinataire/{destinataire}",
     *     summary="Récupérer les transactions par destinataire",
     *     description="Retourne toutes les transactions d'un destinataire spécifique.",
     *     tags={"Transactions"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="destinataire",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Numéro de téléphone du destinataire"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transactions récupérées",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transactions récupérées"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="type de transaction", type="string", example="Transfert d'argent"),
     *                     @OA\Property(property="Destinataire", type="string", example="765463726"),
     *                     @OA\Property(property="Expediteur", type="string", example="776458909"),
     *                     @OA\Property(property="montant", type="number", format="float", example=500),
     *                     @OA\Property(property="Date", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                     @OA\Property(property="Reference", type="string", example="PP250723.2020.B66700"),
     *                     @OA\Property(
     *                         property="metadata",
     *                         type="object",
     *                         @OA\Property(property="derniereModification", type="string", format="date-time", example="2023-06-10T14:30:00Z"),
     *                         @OA\Property(property="version", type="integer", example=1)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Numéro invalide"),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
     */
    public function getByDestinataire() {}
}
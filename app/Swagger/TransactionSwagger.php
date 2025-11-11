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
     *             @OA\Property(property="numero du destinataire", type="string", example="+221880686841", description="Numéro du destinataire au format sénégalais (+221XXXXXXXXX, doit exister en base et être différent de l'expéditeur)"),
     *             @OA\Property(property="montant", type="number", format="float", example=35000, description="Montant de la transaction (doit être positif et supérieur à 0.01)"),
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
     *                 @OA\Property(property="id", type="string", example="cff03e8c-4020-429d-9181-92dcf970165b"),
     *                 @OA\Property(property="type de transaction", type="string", example="Transfert d'argent"),
     *                 @OA\Property(property="Destinataire", type="string", example="+221880686841"),
     *                 @OA\Property(property="Expediteur", type="string", example="+221818930119"),
     *                 @OA\Property(property="montant", type="number", format="float", example=35000),
     *                 @OA\Property(property="Date", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                 @OA\Property(property="Reference", type="string", example="PP231115.2025.BA8F2"),
     *                 @OA\Property(
     *                     property="metadata",
     *                     type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time", example="2025-11-10T15:35:46Z"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Données invalides - compte destinataire inexistant, expéditeur = destinataire, montant invalide, etc."),
     *     @OA\Response(response=401, description="Non autorisé - token manquant ou rôle insuffisant"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function store() {}

    /**
     * @OA\Get(
     *     path="/api/v1/transactions",
     *     summary="Récupérer toutes les transactions de l'utilisateur",
     *     description="Retourne toutes les transactions où l'utilisateur connecté est soit l'expéditeur soit le destinataire.",
     *     tags={"Transactions"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Transactions récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transactions récupérées"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="cff03e8c-4020-429d-9181-92dcf970165b"),
     *                     @OA\Property(property="type de transfere", type="string", example="Transfert d'argent"),
     *                     @OA\Property(property="Numero", type="string", example="+221818930119", description="Numéro de téléphone de l'autre partie (expéditeur si l'utilisateur est destinataire, destinataire si l'utilisateur est expéditeur)"),
     *                     @OA\Property(property="montant", type="number", format="float", example=35000),
     *                     @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                     @OA\Property(
     *                         property="metadata",
     *                         type="object",
     *                         @OA\Property(property="derniereModification", type="string", format="date-time", example="2025-11-10T15:35:46Z"),
     *                         @OA\Property(property="version", type="integer", example=1)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Numéro de téléphone non trouvé dans le token"),
     *     @OA\Response(response=401, description="Non autorisé - token manquant ou rôle insuffisant")
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *     path="/api/v1/transactions/solde",
     *     summary="Récupérer le solde du compte",
     *     description="Calcule et retourne le solde du compte basé sur les transactions (dépôts = +montant, retraits = -montant).",
     *     tags={"Transactions"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Solde calculé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Solde calculé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="solde", type="string", example="27933.58", description="Solde formaté avec 2 décimales"),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="numero_compte", type="string", example="+221818930119"),
     *                 @OA\Property(property="date_calculation", type="string", format="date-time", example="2025-11-10T18:45:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Numéro de téléphone non trouvé dans le token"),
     *     @OA\Response(response=401, description="Non autorisé - token manquant ou rôle insuffisant")
     * )
     */
    public function getSolde() {}

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
     *         description="Transaction récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transaction récupérée"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="cff03e8c-4020-429d-9181-92dcf970165b"),
     *                 @OA\Property(property="type de transaction", type="string", example="Transfert d'argent"),
     *                 @OA\Property(property="Destinataire", type="string", example="+221880686841"),
     *                 @OA\Property(property="Expediteur", type="string", example="+221818930119"),
     *                 @OA\Property(property="montant", type="number", format="float", example=35000),
     *                 @OA\Property(property="Date", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                 @OA\Property(property="Reference", type="string", example="PP231115.2025.BA8F2"),
     *                 @OA\Property(
     *                     property="metadata",
     *                     type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time", example="2025-11-10T15:35:46Z"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Transaction non trouvée"),
     *     @OA\Response(response=401, description="Non autorisé - token manquant ou rôle insuffisant")
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
     *         description="Transactions récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transactions récupérées"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="cff03e8c-4020-429d-9181-92dcf970165b"),
     *                     @OA\Property(property="type de transaction", type="string", example="Transfert d'argent"),
     *                     @OA\Property(property="Destinataire", type="string", example="+221880686841"),
     *                     @OA\Property(property="Expediteur", type="string", example="+221818930119"),
     *                     @OA\Property(property="montant", type="number", format="float", example=35000),
     *                     @OA\Property(property="Date", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                     @OA\Property(property="Reference", type="string", example="PP231115.2025.BA8F2"),
     *                     @OA\Property(
     *                         property="metadata",
     *                         type="object",
     *                         @OA\Property(property="derniereModification", type="string", format="date-time", example="2025-11-10T15:35:46Z"),
     *                         @OA\Property(property="version", type="integer", example=1)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Numéro invalide ou non trouvé"),
     *     @OA\Response(response=401, description="Non autorisé - token manquant ou rôle insuffisant")
     * )
     */
    // public function getByExpediteur() {}

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
     *         description="Transactions récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transactions récupérées"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="cff03e8c-4020-429d-9181-92dcf970165b"),
     *                     @OA\Property(property="type de transaction", type="string", example="Transfert d'argent"),
     *                     @OA\Property(property="Destinataire", type="string", example="+221880686841"),
     *                     @OA\Property(property="Expediteur", type="string", example="+221818930119"),
     *                     @OA\Property(property="montant", type="number", format="float", example=35000),
     *                     @OA\Property(property="Date", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                     @OA\Property(property="Reference", type="string", example="PP231115.2025.BA8F2"),
     *                     @OA\Property(
     *                         property="metadata",
     *                         type="object",
     *                         @OA\Property(property="derniereModification", type="string", format="date-time", example="2025-11-10T15:35:46Z"),
     *                         @OA\Property(property="version", type="integer", example=1)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Numéro invalide ou non trouvé"),
     *     @OA\Response(response=401, description="Non autorisé - token manquant ou rôle insuffisant")
     * )
     */
    // public function getByDestinataire() {}
}
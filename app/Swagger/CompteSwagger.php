<?php

namespace App\Swagger;

/**
 * @OA\Tag(
 *     name="Comptes",
 *     description="Gestion des comptes"
 * )
 */
class CompteSwagger
{
    /**
     * @OA\Post(
     *     path="/api/v1/comptes",
     *     summary="Créer un nouveau compte",
     *     description="Crée un compte pour un utilisateur. Si l'utilisateur n'existe pas, il est créé automatiquement.",
     *     tags={"Comptes"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numero_carte_identite", "numeroTelephone", "type"},
     *             @OA\Property(property="numero_carte_identite", type="string", example="1234567890123", description="Numéro de carte d'identité unique"),
     *             @OA\Property(property="numeroTelephone", type="string", example="+221771234567", description="Numéro de téléphone sénégalais"),
     *             @OA\Property(property="type", type="string", enum={"simple", "marchand"}, example="simple"),
     *             @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}, example="actif"),
     *             @OA\Property(property="codePing", type="string", minLength=4, example="1234"),
     *             @OA\Property(property="nom", type="string", example="Dupont", description="Requis si nouvel utilisateur"),
     *             @OA\Property(property="prenom", type="string", example="Jean", description="Requis si nouvel utilisateur"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="compte",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="10e5ea70-d168-4d14-b6ef-eef90221e630"),
     *                     @OA\Property(property="numeroCompte", type="string", example="NCMTP123456789"),
     *                     @OA\Property(property="type", type="string", example="simple"),
     *                     @OA\Property(property="statut", type="string", example="actif"),
     *                     @OA\Property(property="dateCreation", type="string", format="date"),
     *                     @OA\Property(property="code_qr", type="string", description="QR code en base64")
     *                 ),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="nom", type="string", example="Dupont"),
     *                     @OA\Property(property="prenom", type="string", example="Jean"),
     *                     @OA\Property(property="role", type="string", example="client")
     *                 ),
     *                 @OA\Property(property="is_new_user", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=422, description="Données invalides")
     * )
     */
    // public function store() {}

    /**
     * @OA\Post(
     *     path="/api/v1/comptes/add",
     *     summary="Ajouter un compte supplémentaire pour l'utilisateur connecté",
     *     description="Permet à un utilisateur authentifié d'ajouter un compte supplémentaire en utilisant son profil existant.",
     *     tags={"Comptes"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numeroTelephone", "type"},
     *             @OA\Property(property="numeroTelephone", type="string", example="+221771234567", description="Numéro de téléphone sénégalais"),
     *             @OA\Property(property="type", type="string", enum={"simple", "marchand"}, example="simple"),
     *             @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}, example="actif"),
     *             @OA\Property(property="codePing", type="string", minLength=4, example="1234")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte supplémentaire créé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte supplémentaire créé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="compte",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="10e5ea70-d168-4d14-b6ef-eef90221e630"),
     *                     @OA\Property(property="numeroCompte", type="string", example="NCMTP123456789"),
     *                     @OA\Property(property="type", type="string", example="simple"),
     *                     @OA\Property(property="statut", type="string", example="actif"),
     *                     @OA\Property(property="dateCreation", type="string", format="date"),
     *                     @OA\Property(property="code_qr", type="string", description="QR code en base64")
     *                 ),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="nom", type="string", example="Dupont"),
     *                     @OA\Property(property="prenom", type="string", example="Jean"),
     *                     @OA\Property(property="numero_carte_identite", type="string", example="1234567890123"),
     *                     @OA\Property(property="role", type="string", example="client")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=422, description="Données invalides")
     * )
     */
    // public function add() {}

    /**
     * @OA\Get(
     *     path="/api/v1/comptes",
     *     summary="Lister les comptes selon le rôle",
     *     description="Retourne le compte du client connecté ou tous les comptes si c’est un admin. Supporte les filtres et la recherche.",
     *     tags={"Comptes"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"simple", "marchand"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "bloque", "ferme"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Rechercher par numéro de compte, nom, prénom ou téléphone",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comptes récupérés",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comptes récupérés"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                     @OA\Items(
     *                     @OA\Property(property="id", type="string", example="10e5ea70-d168-4d14-b6ef-eef90221e630"),
     *                     @OA\Property(property="numeroCompte", type="string", example="NCMTP123456789"),
     *                     @OA\Property(property="type", type="string", example="marchand"),
     *                     @OA\Property(property="statut", type="string", example="actif"),
     *                     @OA\Property(property="dateCreation", type="string", format="date"),
     *                     @OA\Property(property="code_qr", type="string", description="QR code en base64", example="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA..."),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
     */
    public function index() {}
}

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
     * @OA\Get(
     *     path="/api/v1/comptes",
     *     summary="Lister les comptes selon le rôle",
     *     description="Retourne le compte du client connecté ou tous les comptes si c’est un admin.",
     *     tags={"Comptes"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Comptes récupérés",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="comptes recuperer"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="10e5ea70-d168-4d14-b6ef-eef90221e630"),
     *                     @OA\Property(property="numeroCompte", type="string", example="T3YK5AED7K"),
     *                     @OA\Property(property="type", type="string", example="marchand"),
     *                     @OA\Property(property="statut", type="string", example="actif"),
     *                     @OA\Property(property="dateCreation", type="string", format="date-time"),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
     */
    public function index() {}
}

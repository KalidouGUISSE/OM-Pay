<?php

namespace App\Swagger;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentification"
 * )
 */
class AuthSwagger
{

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Connexion d’un utilisateur",
     *     description="Permet à un utilisateur de se connecter avec son numéro de téléphone et son code PIN.",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numeroTelephone", "codePing"},
     *             @OA\Property(property="numeroTelephone", type="string", example="7022721314"),
     *             @OA\Property(property="codePing", type="string", example="1234")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Connexion réussie"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="access_token", type="string", example="1|xxxxxxxxxx"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="role", type="string", example="client"),
     *                 @OA\Property(property="compte_id", type="string", example="10e5ea70-d168-4d14-b6ef-eef90221e630")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Numéro de téléphone ou code PIN invalide")
     *         )
     *     )
     * )
     */
    public function login(){}


}

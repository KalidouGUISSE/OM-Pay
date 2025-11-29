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
     *     path="/api/v1/auth/initiate-login",
     *     summary="Initier la connexion avec OTP",
     *     description="Permet d'initier la connexion en envoyant un numéro de téléphone. Un code OTP sera généré et envoyé automatiquement par SMS.",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numeroTelephone"},
     *             @OA\Property(property="numeroTelephone", type="string", example="+221784458786", description="Numéro de téléphone sénégalais valide")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP envoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP envoyé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="temp_token", type="string", example="eyJpdiI6Imtka1RhdzBtVzRObkNoYktrS3NGWWc9PSIs..."),
     *                 @OA\Property(property="otp", type="string", example="805826", description="Code OTP généré (pour développement/tests)"),
     *                 @OA\Property(property="message", type="string", example="OTP envoyé avec succès"),
     *                 @OA\Property(property="expires_in", type="integer", example=300, description="Durée de validité en secondes")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Numéro de téléphone non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Numéro de téléphone non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Compte inactif",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Votre compte n'est pas actif")
     *         )
     *     )
     * )
     */
    public function initiateLogin(){}

    /**
     * @OA\Post(
     *     path="/api/v1/auth/verify-otp",
     *     summary="Vérifier le code OTP",
     *     description="Permet de vérifier le code OTP reçu et d'obtenir les tokens d'authentification.",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "otp"},
     *             @OA\Property(property="token", type="string", example="eyJpdiI6Imtka1RhdzBtVzRObkNoYktrS3NGWWc9PSIs...", description="Token temporaire reçu lors de l'initiation"),
     *             @OA\Property(property="otp", type="string", example="805826", description="Code OTP de 6 chiffres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentification réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Authentification réussie"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="access_token", type="string", example="21|17vf3MfQS8c64IZvf4j5szpBMkPQF7uSLYoa70jkc33515bb"),
     *                 @OA\Property(property="refresh_token", type="string", example="22|refresh_token_here"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600, description="Durée de validité de l'access token en secondes")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="OTP invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Code OTP invalide ou expiré")
     *         )
     *     )
     * )
     */
    public function verifyOtp(){}

    /**
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     summary="Informations de l'utilisateur authentifié avec dernières transactions",
     *     description="Retourne les informations détaillées de l'utilisateur, de son compte et les 10 dernières transactions effectuées sur ce compte.",
     *     tags={"Authentification"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Informations récupérées"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="e8b26bdb-6568-4c57-85ab-6b24de56de1e"),
     *                     @OA\Property(property="nom", type="string", example="Kalidou"),
     *                     @OA\Property(property="prenom", type="string", example="Utilisateur"),
     *                     @OA\Property(property="role", type="string", example="client")
     *                 ),
     *                 @OA\Property(
     *                     property="compte",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="5d42b58b-4b5d-4782-b13f-c41f758b7258"),
     *                     @OA\Property(property="numero_compte", type="string", example="NCMTPKAL931"),
     *                     @OA\Property(property="numero_telephone", type="string", example="+221784458786"),
     *                     @OA\Property(property="type", type="string", example="simple"),
     *                     @OA\Property(property="statut", type="string", example="actif"),
     *                     @OA\Property(property="date_creation", type="string", format="date-time", example="2025-11-11T00:00:00Z")
     *                 ),
     *                 @OA\Property(
     *                     property="dernieres_transactions",
     *                     type="array",
     *                     description="Les 10 dernières transactions du compte",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", example="db55cea5-57f3-4458-a767-347624d27f1e"),
     *                         @OA\Property(property="type_transaction", type="string", example="Dépôt"),
     *                         @OA\Property(property="montant", type="string", example="353313.00"),
     *                         @OA\Property(property="date", type="string", format="date-time", example="2025-11-08T15:51:34.000000Z"),
     *                         @OA\Property(property="reference", type="string", example="PP2511.2025.BA3WEA"),
     *                         @OA\Property(property="contrepartie", type="string", example="+221000000000", description="Numéro de téléphone de l'autre partie"),
     *                         @OA\Property(property="direction", type="string", enum={"credit", "debit"}, example="credit", description="Direction de la transaction pour ce compte")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Utilisateur non authentifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     )
     * )
     */
    public function me(){}

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Connexion traditionnelle (numéro + PIN)",
     *     description="Permet à un utilisateur de se connecter avec son numéro de téléphone et son code PIN. Méthode maintenue pour compatibilité.",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numeroTelephone", "codePing"},
     *             @OA\Property(property="numeroTelephone", type="string", example="+221818930119", description="Numéro de téléphone sénégalais valide"),
     *             @OA\Property(property="codePing", type="string", example="1234", description="Code PIN du compte")
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
     *                 @OA\Property(property="compte_id", type="string", example="228e7d7a-937b-40dd-88d7-ff8fa4d334f4")
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
    // public function login(){}


}

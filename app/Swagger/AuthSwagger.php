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
     *     description="Permet d'initier la connexion en envoyant un numéro de téléphone. Un code OTP sera généré et envoyé.",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numeroTelephone"},
     *             @OA\Property(property="numeroTelephone", type="string", example="+221818930119", description="Numéro de téléphone sénégalais valide")
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
     *     description="Permet de vérifier le code OTP reçu et d'obtenir un token d'authentification complet.",
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
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="d4f1ab82-a86e-4975-aa66-5ef804bfa246"),
     *                     @OA\Property(property="nom", type="string", example="Ondricka"),
     *                     @OA\Property(property="prenom", type="string", example="Tanya"),
     *                     @OA\Property(property="role", type="string", example="client")
     *                 ),
     *                 @OA\Property(property="compte_id", type="string", example="228e7d7a-937b-40dd-88d7-ff8fa4d334f4"),
     *                 @OA\Property(property="numero_telephone", type="string", example="+221818930119"),
     *                 @OA\Property(property="role", type="string", example="client"),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
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
    public function login(){}


}

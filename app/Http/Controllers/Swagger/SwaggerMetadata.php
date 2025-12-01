<?php

namespace App\Http\Controllers\Swagger;

/**
 * @OA\Info(
 *     title="API OM Pay",
 *     version="1.0.0",
 *     description="Documentation interactive de l’API OM Pay construite avec Laravel et Sanctum"
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Serveur dynamique (local ou production)"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class SwaggerMetadata
{
    // Classe vide, juste pour Swagger
}

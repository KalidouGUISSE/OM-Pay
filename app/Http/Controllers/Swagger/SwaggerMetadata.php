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
 *     url="http://localhost:8000",
 *     description="Serveur local"
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

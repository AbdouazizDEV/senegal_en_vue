<?php

namespace App\Presentation\Http\Controllers\Api\V1;

use OpenApi\Attributes as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Sénégal en Vue API",
 *     description="API REST pour la plateforme de tourisme local Sénégal en Vue. Documentation complète des endpoints d'authentification, gestion des expériences, réservations et paiements."
 * )
 * 
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Serveur de développement local"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Entrez votre token JWT obtenu lors de la connexion. Format: Bearer {token}"
 * )
 * 
 * @OA\Tag(
 *     name="Authentification",
 *     description="Endpoints d'authentification et de gestion des utilisateurs"
 * )
 */
class OpenApiController
{
    //
}



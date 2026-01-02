<?php

namespace App\Presentation\Http\Controllers\Api\V1\Auth;

use App\Presentation\Http\Controllers\Controller;

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
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     required={"id", "uuid", "name", "email", "role", "status"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="uuid", type="string", example="7514c633-716e-4d88-9ef5-46f8fc6dc714"),
 *     @OA\Property(property="name", type="string", example="Amadou Diallo"),
 *     @OA\Property(property="email", type="string", format="email", example="amadou.diallo@example.com"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+221771234567"),
 *     @OA\Property(property="role", type="string", enum={"admin", "traveler", "provider", "institution"}, example="traveler"),
 *     @OA\Property(property="role_label", type="string", example="Voyageur"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "suspended", "pending_verification", "verified"}, example="active"),
 *     @OA\Property(property="status_label", type="string", example="Actif"),
 *     @OA\Property(property="avatar", type="string", nullable=true),
 *     @OA\Property(property="bio", type="string", nullable=true),
 *     @OA\Property(property="preferences", type="object", nullable=true),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="phone_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="last_login_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Tag(
 *     name="Authentification",
 *     description="Endpoints d'authentification et de gestion des utilisateurs"
 * )
 * 
 * @OA\Tag(
 *     name="Administration",
 *     description="Endpoints d'administration pour la gestion de la plateforme"
 * )
 */
abstract class Controller extends \App\Presentation\Http\Controllers\Controller
{
    //
}


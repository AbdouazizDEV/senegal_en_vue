<?php

namespace App\Presentation\Http\Controllers\Api\V1\Auth;

use App\Domain\User\Enums\UserStatus;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;
use App\Presentation\Http\Controllers\Api\V1\Auth\BaseController;
use App\Presentation\Http\Resources\UserResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends BaseController
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Connexion utilisateur
     */
    #[OA\Post(
        path: '/api/v1/auth/login',
        summary: 'Connexion utilisateur',
        description: 'Authentifie un utilisateur et retourne un token JWT',
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amadou.diallo@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Connexion réussie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Connexion réussie'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'user',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'uuid', type: 'string', example: '7514c633-716e-4d88-9ef5-46f8fc6dc714'),
                                        new OA\Property(property: 'name', type: 'string', example: 'Amadou Diallo'),
                                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amadou.diallo@example.com'),
                                        new OA\Property(property: 'role', type: 'string', example: 'traveler'),
                                        new OA\Property(property: 'status', type: 'string', example: 'active'),
                                    ]
                                ),
                                new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),
                                new OA\Property(property: 'token_type', type: 'string', example: 'bearer'),
                                new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Identifiants incorrects'),
            new OA\Response(response: 403, description: 'Compte non actif'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Données invalides', 422, $validator->errors()->toArray());
        }

        $credentials = $request->only('email', 'password');

        // Tentative de connexion
        if (!$token = auth('api')->attempt($credentials)) {
            return ApiResponse::error('Identifiants incorrects', 401);
        }

        $user = auth('api')->user();

        // Vérifier si l'utilisateur peut se connecter
        if (!$user->canLogin()) {
            auth('api')->logout();
            return ApiResponse::error('Votre compte n\'est pas actif. Veuillez contacter le support.', 403);
        }

        // Mettre à jour la dernière connexion
        $user->update(['last_login_at' => now()]);

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ], 'Connexion réussie');
    }

    /**
     * Déconnexion utilisateur
     */
    #[OA\Post(
        path: '/api/v1/auth/logout',
        summary: 'Déconnexion utilisateur',
        description: 'Déconnecte l\'utilisateur et invalide le token JWT',
        tags: ['Authentification'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Déconnexion réussie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Déconnexion réussie'),
                        new OA\Property(property: 'data', type: 'null'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return ApiResponse::success(null, 'Déconnexion réussie');
    }

    /**
     * Rafraîchir le token JWT
     */
    #[OA\Post(
        path: '/api/v1/auth/refresh',
        summary: 'Rafraîchir le token JWT',
        description: 'Génère un nouveau token JWT pour l\'utilisateur authentifié',
        tags: ['Authentification'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token rafraîchi avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Token rafraîchi'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'user',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'uuid', type: 'string', example: '7514c633-716e-4d88-9ef5-46f8fc6dc714'),
                                        new OA\Property(property: 'name', type: 'string', example: 'Amadou Diallo'),
                                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amadou.diallo@example.com'),
                                        new OA\Property(property: 'role', type: 'string', example: 'traveler'),
                                        new OA\Property(property: 'status', type: 'string', example: 'active'),
                                    ]
                                ),
                                new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),
                                new OA\Property(property: 'token_type', type: 'string', example: 'bearer'),
                                new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token invalide ou expiré'),
        ]
    )]
    public function refresh(): JsonResponse
    {
        try {
            $token = auth('api')->refresh();
            $user = auth('api')->user();

            return ApiResponse::success([
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ], 'Token rafraîchi');
        } catch (\Exception $e) {
            return ApiResponse::error('Impossible de rafraîchir le token', 401);
        }
    }
}

<?php

namespace App\Presentation\Http\Controllers\Api\V1\Auth;

use App\Application\User\Commands\RegisterProviderCommand;
use App\Application\User\Commands\RegisterTravelerCommand;
use App\Application\User\Handlers\RegisterProviderHandler;
use App\Application\User\Handlers\RegisterTravelerHandler;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Auth\RegisterProviderRequest;
use App\Presentation\Http\Requests\Auth\RegisterTravelerRequest;
use App\Presentation\Http\Resources\UserResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class RegisterController extends Controller
{
    public function __construct(
        private RegisterTravelerHandler $registerTravelerHandler,
        private RegisterProviderHandler $registerProviderHandler
    ) {}

    /**
     * Enregistrer un nouveau voyageur
     */
    #[OA\Post(
        path: '/api/v1/auth/register/traveler',
        summary: 'Inscription d\'un voyageur',
        description: 'Crée un nouveau compte utilisateur avec le rôle voyageur',
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'phone', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Amadou Diallo'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amadou.diallo@example.com'),
                    new OA\Property(property: 'phone', type: 'string', example: '+221771234567'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(
                        property: 'preferences',
                        type: 'object',
                        example: ['language' => 'fr', 'notifications' => true]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Inscription réussie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Inscription réussie'),
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
                                        new OA\Property(property: 'status', type: 'string', example: 'pending_verification'),
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
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function registerTraveler(RegisterTravelerRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $command = new RegisterTravelerCommand(
            name: $validated['name'],
            email: $validated['email'],
            phone: $validated['phone'],
            password: $validated['password'],
            preferences: $validated['preferences'] ?? null,
        );

        $user = $this->registerTravelerHandler->handle($command);

        $token = auth('api')->login($user);

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ], 'Inscription réussie', 201);
    }

    /**
     * Enregistrer un nouveau prestataire
     */
    #[OA\Post(
        path: '/api/v1/auth/register/provider',
        summary: 'Inscription d\'un prestataire',
        description: 'Crée un nouveau compte utilisateur avec le rôle prestataire',
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'phone', 'password', 'password_confirmation', 'business_name', 'address', 'city', 'region'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Mamadou Ndiaye'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'mamadou.ndiaye@example.com'),
                    new OA\Property(property: 'phone', type: 'string', example: '+221772345678'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'business_name', type: 'string', example: 'Safari Sénégal'),
                    new OA\Property(property: 'address', type: 'string', example: 'Route de la Corniche, Ouakam'),
                    new OA\Property(property: 'city', type: 'string', example: 'Dakar'),
                    new OA\Property(
                        property: 'region',
                        type: 'string',
                        enum: ['dakar', 'thies', 'saint-louis', 'diourbel', 'fatick', 'kaffrine', 'kaolack', 'kedougou', 'kolda', 'louga', 'matam', 'sedhiou', 'tambacounda', 'ziguinchor'],
                        example: 'dakar'
                    ),
                    new OA\Property(property: 'bio', type: 'string', nullable: true, example: 'Guide touristique expérimenté'),
                    new OA\Property(property: 'business_registration_number', type: 'string', nullable: true, example: 'SN-DKR-2024-001'),
                    new OA\Property(
                        property: 'preferences',
                        type: 'object',
                        nullable: true,
                        example: ['language' => 'fr']
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Inscription réussie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Inscription réussie'),
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
                                        new OA\Property(property: 'status', type: 'string', example: 'pending_verification'),
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
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function registerProvider(RegisterProviderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $command = new RegisterProviderCommand(
            name: $validated['name'],
            email: $validated['email'],
            phone: $validated['phone'],
            password: $validated['password'],
            businessName: $validated['business_name'],
            address: $validated['address'],
            city: $validated['city'],
            region: $validated['region'],
            bio: $validated['bio'] ?? null,
            businessRegistrationNumber: $validated['business_registration_number'] ?? null,
            preferences: $validated['preferences'] ?? null,
        );

        $user = $this->registerProviderHandler->handle($command);

        $token = auth('api')->login($user);

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ], 'Inscription réussie', 201);
    }
}

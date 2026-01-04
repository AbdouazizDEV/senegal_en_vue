<?php

namespace App\Presentation\Http\Controllers\Api\V1\Traveler;

use App\Application\Traveler\Commands\UpdateProfileCommand;
use App\Application\Traveler\Commands\UpdateProfilePhotoCommand;
use App\Application\Traveler\Handlers\GetProfileHandler;
use App\Application\Traveler\Handlers\UpdateProfileHandler;
use App\Application\Traveler\Handlers\UpdateProfilePhotoHandler;
use App\Application\Traveler\Queries\GetProfileQuery;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Resources\UserResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Voyageur", description: "Endpoints pour les voyageurs")]
class ProfileController extends Controller
{
    public function __construct(
        private GetProfileHandler $getProfileHandler,
        private UpdateProfileHandler $updateProfileHandler,
        private UpdateProfilePhotoHandler $updateProfilePhotoHandler
    ) {}

    /**
     * Consulter mon profil
     */
    #[OA\Get(
        path: '/api/v1/traveler/profile',
        summary: 'Consulter mon profil',
        description: 'Récupère les informations du profil du voyageur authentifié',
        tags: ['Voyageur'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Profil récupéré avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'uuid', type: 'string'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'email', type: 'string'),
                                new OA\Property(property: 'phone', type: 'string', nullable: true),
                                new OA\Property(property: 'role', type: 'string'),
                                new OA\Property(property: 'status', type: 'string'),
                                new OA\Property(property: 'avatar', type: 'string', nullable: true),
                                new OA\Property(property: 'bio', type: 'string', nullable: true),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function show(Request $request): JsonResponse
    {
        $query = new GetProfileQuery(userId: auth()->id());
        $user = $this->getProfileHandler->handle($query);

        return ApiResponse::success(new UserResource($user), 'Profil récupéré avec succès');
    }

    /**
     * Mettre à jour mon profil
     */
    #[OA\Put(
        path: '/api/v1/traveler/profile',
        summary: 'Mettre à jour mon profil',
        description: 'Met à jour les informations du profil du voyageur authentifié',
        tags: ['Voyageur'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Sophie Dubois'),
                    new OA\Property(property: 'phone', type: 'string', example: '+221774567890'),
                    new OA\Property(property: 'bio', type: 'string', nullable: true, example: 'Passionnée de voyages et de découvertes'),
                    new OA\Property(property: 'preferences', type: 'object', nullable: true, example: ['language' => 'fr', 'currency' => 'XOF']),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil mis à jour avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Profil mis à jour avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'uuid', type: 'string'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'email', type: 'string'),
                                new OA\Property(property: 'phone', type: 'string', nullable: true),
                                new OA\Property(property: 'role', type: 'string'),
                                new OA\Property(property: 'status', type: 'string'),
                                new OA\Property(property: 'avatar', type: 'string', nullable: true),
                                new OA\Property(property: 'bio', type: 'string', nullable: true),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . auth()->id(),
            'bio' => 'nullable|string|max:1000',
            'preferences' => 'nullable|array',
        ]);

        try {
            $command = new UpdateProfileCommand(userId: auth()->id(), data: $validated);
            $user = $this->updateProfileHandler->handle($command);

            return ApiResponse::success(new UserResource($user), 'Profil mis à jour avec succès');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Ajouter/modifier photo de profil
     */
    #[OA\Post(
        path: '/api/v1/traveler/profile/photo',
        summary: 'Ajouter/modifier photo de profil',
        description: 'Met à jour la photo de profil du voyageur. L\'image doit être uploadée via Cloudinary et l\'URL stockée.',
        tags: ['Voyageur'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['photo_url'],
                    properties: [
                        new OA\Property(property: 'photo_url', type: 'string', format: 'uri', description: 'URL Cloudinary de la photo de profil', example: 'https://res.cloudinary.com/example/image/upload/v1/profile.jpg'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Photo de profil mise à jour avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Photo de profil mise à jour avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'uuid', type: 'string'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'email', type: 'string'),
                                new OA\Property(property: 'avatar', type: 'string', nullable: true),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function updatePhoto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'photo_url' => 'required|url',
        ]);

        try {
            $command = new UpdateProfilePhotoCommand(userId: auth()->id(), photoUrl: $validated['photo_url']);
            $user = $this->updateProfilePhotoHandler->handle($command);

            return ApiResponse::success(new UserResource($user), 'Photo de profil mise à jour avec succès');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }
}


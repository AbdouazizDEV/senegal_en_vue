<?php

namespace App\Presentation\Http\Controllers\Api\V1\Traveler;

use App\Application\Discovery\Commands\UpdatePreferencesCommand;
use App\Application\Discovery\Handlers\GetHiddenGemsHandler;
use App\Application\Discovery\Handlers\GetPersonalizedSuggestionsHandler;
use App\Application\Discovery\Handlers\GetTrendingExperiencesHandler;
use App\Application\Discovery\Handlers\UpdatePreferencesHandler;
use App\Application\Discovery\Queries\GetHiddenGemsQuery;
use App\Application\Discovery\Queries\GetPersonalizedSuggestionsQuery;
use App\Application\Discovery\Queries\GetTrendingExperiencesQuery;
use App\Presentation\Http\Controllers\Api\V1\Auth\Controller;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Voyageur - Découverte",
    description: "Endpoints pour le mode découverte et les suggestions personnalisées"
)]
class DiscoveryController extends Controller
{
    public function __construct(
        private GetPersonalizedSuggestionsHandler $getPersonalizedSuggestionsHandler,
        private GetTrendingExperiencesHandler $getTrendingExperiencesHandler,
        private GetHiddenGemsHandler $getHiddenGemsHandler,
        private UpdatePreferencesHandler $updatePreferencesHandler
    ) {}

    /**
     * Suggestions personnalisées
     */
    #[OA\Get(
        path: '/api/v1/traveler/discovery/suggestions',
        summary: 'Obtenir des suggestions personnalisées',
        description: 'Récupère des suggestions d\'expériences personnalisées basées sur les préférences de l\'utilisateur.',
        tags: ['Voyageur - Découverte'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'Nombre maximum de suggestions à retourner',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 10, minimum: 1, maximum: 50)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Suggestions personnalisées récupérées avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Suggestions personnalisées récupérées avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Experience')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function suggestions(Request $request): JsonResponse
    {
        $query = new GetPersonalizedSuggestionsQuery(
            userId: auth()->id(),
            limit: (int) $request->input('limit', 10)
        );

        $suggestions = $this->getPersonalizedSuggestionsHandler->handle($query);

        return ApiResponse::success($suggestions->values(), 'Suggestions personnalisées récupérées avec succès');
    }

    /**
     * Définir mes préférences
     */
    #[OA\Post(
        path: '/api/v1/traveler/discovery/preferences',
        summary: 'Définir les préférences de découverte',
        description: 'Permet au voyageur de définir ses préférences pour recevoir des suggestions personnalisées.',
        tags: ['Voyageur - Découverte'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'preferred_types',
                        type: 'array',
                        items: new OA\Items(type: 'string', enum: ['activity', 'tour', 'workshop', 'event', 'accommodation', 'restaurant']),
                        nullable: true,
                        example: ['tour', 'activity']
                    ),
                    new OA\Property(
                        property: 'preferred_regions',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        nullable: true,
                        example: ['Dakar', 'Saint-Louis']
                    ),
                    new OA\Property(
                        property: 'preferred_tags',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        nullable: true,
                        example: ['culture', 'histoire', 'nature']
                    ),
                    new OA\Property(property: 'min_price', type: 'number', format: 'float', nullable: true, example: 10000),
                    new OA\Property(property: 'max_price', type: 'number', format: 'float', nullable: true, example: 50000),
                    new OA\Property(property: 'min_duration_minutes', type: 'integer', nullable: true, example: 60),
                    new OA\Property(property: 'max_duration_minutes', type: 'integer', nullable: true, example: 480),
                    new OA\Property(property: 'preferred_participants', type: 'integer', nullable: true, example: 4),
                    new OA\Property(
                        property: 'budget_range',
                        type: 'array',
                        items: new OA\Items(type: 'string', enum: ['low', 'medium', 'high']),
                        nullable: true,
                        example: ['low', 'medium']
                    ),
                    new OA\Property(
                        property: 'interests',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        nullable: true,
                        example: ['patrimoine', 'gastronomie']
                    ),
                    new OA\Property(property: 'prefer_featured', type: 'boolean', nullable: true, example: true),
                    new OA\Property(property: 'prefer_eco_friendly', type: 'boolean', nullable: true, example: false),
                    new OA\Property(property: 'prefer_certified_providers', type: 'boolean', nullable: true, example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Préférences mises à jour avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Préférences mises à jour avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'uuid', type: 'string'),
                                new OA\Property(property: 'preferred_types', type: 'array', items: new OA\Items(type: 'string')),
                                new OA\Property(property: 'preferred_regions', type: 'array', items: new OA\Items(type: 'string')),
                                new OA\Property(property: 'preferred_tags', type: 'array', items: new OA\Items(type: 'string')),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreurs de validation'),
        ]
    )]
    public function preferences(Request $request): JsonResponse
    {
        $command = new UpdatePreferencesCommand(
            userId: auth()->id(),
            preferredTypes: $request->input('preferred_types'),
            preferredRegions: $request->input('preferred_regions'),
            preferredTags: $request->input('preferred_tags'),
            minPrice: $request->input('min_price') ? (float) $request->input('min_price') : null,
            maxPrice: $request->input('max_price') ? (float) $request->input('max_price') : null,
            minDurationMinutes: $request->input('min_duration_minutes') ? (int) $request->input('min_duration_minutes') : null,
            maxDurationMinutes: $request->input('max_duration_minutes') ? (int) $request->input('max_duration_minutes') : null,
            preferredParticipants: $request->input('preferred_participants') ? (int) $request->input('preferred_participants') : null,
            budgetRange: $request->input('budget_range'),
            interests: $request->input('interests'),
            preferFeatured: $request->input('prefer_featured'),
            preferEcoFriendly: $request->input('prefer_eco_friendly'),
            preferCertifiedProviders: $request->input('prefer_certified_providers'),
        );

        $preferences = $this->updatePreferencesHandler->handle($command);

        return ApiResponse::success($preferences, 'Préférences mises à jour avec succès');
    }

    /**
     * Expériences tendances
     */
    #[OA\Get(
        path: '/api/v1/traveler/discovery/trending',
        summary: 'Obtenir les expériences tendances',
        description: 'Récupère les expériences les plus populaires basées sur les réservations, vues et notations.',
        tags: ['Voyageur - Découverte'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'Nombre maximum d\'expériences à retourner',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 10, minimum: 1, maximum: 50)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Expériences tendances récupérées avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Expériences tendances récupérées avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Experience')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function trending(Request $request): JsonResponse
    {
        $query = new GetTrendingExperiencesQuery(
            limit: (int) $request->input('limit', 10)
        );

        $experiences = $this->getTrendingExperiencesHandler->handle($query);

        return ApiResponse::success($experiences->values(), 'Expériences tendances récupérées avec succès');
    }

    /**
     * Pépites cachées
     */
    #[OA\Get(
        path: '/api/v1/traveler/discovery/hidden-gems',
        summary: 'Découvrir les pépites cachées',
        description: 'Récupère des expériences peu connues mais bien notées, idéales pour découvrir des trésors cachés.',
        tags: ['Voyageur - Découverte'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'Nombre maximum d\'expériences à retourner',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 10, minimum: 1, maximum: 50)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pépites cachées récupérées avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Pépites cachées récupérées avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Experience')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function hiddenGems(Request $request): JsonResponse
    {
        $query = new GetHiddenGemsQuery(
            userId: auth()->id(),
            limit: (int) $request->input('limit', 10)
        );

        $experiences = $this->getHiddenGemsHandler->handle($query);

        return ApiResponse::success($experiences->values(), 'Pépites cachées récupérées avec succès');
    }
}


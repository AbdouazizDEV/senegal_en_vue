<?php

namespace App\Presentation\Http\Controllers\Api\V1\Traveler;

use App\Application\Favorite\Commands\AddFavoriteCommand;
use App\Application\Favorite\Commands\RemoveFavoriteCommand;
use App\Application\Favorite\Handlers\AddFavoriteHandler;
use App\Application\Favorite\Handlers\GetFavoritesAlertsHandler;
use App\Application\Favorite\Handlers\GetFavoritesHandler;
use App\Application\Favorite\Handlers\RemoveFavoriteHandler;
use App\Application\Favorite\Queries\GetFavoritesAlertsQuery;
use App\Application\Favorite\Queries\GetFavoritesQuery;
use App\Presentation\Http\Controllers\Api\V1\Auth\BaseController;
use App\Presentation\Http\Resources\ExperienceResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Voyageur - Favoris",
    description: "Endpoints pour la gestion des expériences favorites"
)]
class FavoriteController extends BaseController
{
    public function __construct(
        private GetFavoritesHandler $getFavoritesHandler,
        private AddFavoriteHandler $addFavoriteHandler,
        private RemoveFavoriteHandler $removeFavoriteHandler,
        private GetFavoritesAlertsHandler $getFavoritesAlertsHandler
    ) {}

    /**
     * Lister mes expériences favorites
     */
    #[OA\Get(
        path: '/api/v1/traveler/favorites',
        summary: 'Lister mes expériences favorites',
        description: 'Récupère la liste paginée des expériences favorites du voyageur authentifié.',
        tags: ['Voyageur - Favoris'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Numéro de page',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: 'Nombre d\'éléments par page',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15, minimum: 1, maximum: 100)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des favoris récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Liste des favoris récupérée avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'uuid', type: 'string'),
                                    new OA\Property(property: 'experience', ref: '#/components/schemas/Experience'),
                                    new OA\Property(property: 'notify_on_price_drop', type: 'boolean'),
                                    new OA\Property(property: 'notify_on_availability', type: 'boolean'),
                                    new OA\Property(property: 'notify_on_new_reviews', type: 'boolean'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'meta', type: 'object'),
                        new OA\Property(property: 'links', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = new GetFavoritesQuery(
            userId: auth()->id(),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15)
        );

        $favorites = $this->getFavoritesHandler->handle($query);

        return ApiResponse::paginated(
            $favorites,
            'Liste des favoris récupérée avec succès',
            function ($favorite) {
                return [
                    'id' => $favorite->id,
                    'uuid' => $favorite->uuid,
                    'experience' => new ExperienceResource($favorite->experience),
                    'notify_on_price_drop' => $favorite->notify_on_price_drop,
                    'notify_on_availability' => $favorite->notify_on_availability,
                    'notify_on_new_reviews' => $favorite->notify_on_new_reviews,
                    'created_at' => $favorite->created_at->toIso8601String(),
                ];
            }
        );
    }

    /**
     * Ajouter aux favoris
     */
    #[OA\Post(
        path: '/api/v1/traveler/favorites/{experienceId}',
        summary: 'Ajouter une expérience aux favoris',
        description: 'Ajoute une expérience à la liste des favoris du voyageur avec des options de notification.',
        tags: ['Voyageur - Favoris'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'experienceId',
                in: 'path',
                description: 'ID de l\'expérience à ajouter aux favoris',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'notify_on_price_drop', type: 'boolean', default: false, example: true),
                    new OA\Property(property: 'notify_on_availability', type: 'boolean', default: false, example: true),
                    new OA\Property(property: 'notify_on_new_reviews', type: 'boolean', default: false, example: false),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Expérience ajoutée aux favoris avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Expérience ajoutée aux favoris avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'uuid', type: 'string'),
                                new OA\Property(property: 'experience_id', type: 'integer'),
                                new OA\Property(property: 'notify_on_price_drop', type: 'boolean'),
                                new OA\Property(property: 'notify_on_availability', type: 'boolean'),
                                new OA\Property(property: 'notify_on_new_reviews', type: 'boolean'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Expérience non trouvée'),
            new OA\Response(response: 409, description: 'Expérience déjà dans les favoris'),
        ]
    )]
    public function store(Request $request, int $experienceId): JsonResponse
    {
        try {
            $command = new AddFavoriteCommand(
                userId: auth()->id(),
                experienceId: $experienceId,
                notifyOnPriceDrop: $request->input('notify_on_price_drop', false),
                notifyOnAvailability: $request->input('notify_on_availability', false),
                notifyOnNewReviews: $request->input('notify_on_new_reviews', false),
            );

            $favorite = $this->addFavoriteHandler->handle($command);

            return ApiResponse::success([
                'id' => $favorite->id,
                'uuid' => $favorite->uuid,
                'experience_id' => $favorite->experience_id,
                'notify_on_price_drop' => $favorite->notify_on_price_drop,
                'notify_on_availability' => $favorite->notify_on_availability,
                'notify_on_new_reviews' => $favorite->notify_on_new_reviews,
            ], 'Expérience ajoutée aux favoris avec succès', 201);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'déjà')) {
                return ApiResponse::error($e->getMessage(), 409);
            }
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Retirer des favoris
     */
    #[OA\Delete(
        path: '/api/v1/traveler/favorites/{experienceId}',
        summary: 'Retirer une expérience des favoris',
        description: 'Retire une expérience de la liste des favoris du voyageur.',
        tags: ['Voyageur - Favoris'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'experienceId',
                in: 'path',
                description: 'ID de l\'expérience à retirer des favoris',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Expérience retirée des favoris avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Expérience retirée des favoris avec succès'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Expérience non trouvée dans les favoris'),
        ]
    )]
    public function destroy(int $experienceId): JsonResponse
    {
        try {
            $command = new RemoveFavoriteCommand(
                userId: auth()->id(),
                experienceId: $experienceId
            );

            $this->removeFavoriteHandler->handle($command);

            return ApiResponse::success(null, 'Expérience retirée des favoris avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Alertes sur mes favoris
     */
    #[OA\Get(
        path: '/api/v1/traveler/favorites/alerts',
        summary: 'Obtenir les alertes sur mes favoris',
        description: 'Récupère les alertes actives pour les expériences favorites (baisse de prix, disponibilité, nouveaux avis).',
        tags: ['Voyageur - Favoris'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Alertes récupérées avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Alertes récupérées avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'favorite_id', type: 'integer'),
                                    new OA\Property(property: 'experience', ref: '#/components/schemas/Experience'),
                                    new OA\Property(
                                        property: 'alerts',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'price_drop', type: 'boolean'),
                                            new OA\Property(property: 'availability', type: 'boolean'),
                                            new OA\Property(property: 'new_reviews', type: 'boolean'),
                                        ]
                                    ),
                                    new OA\Property(property: 'notified_at', type: 'string', format: 'date-time', nullable: true),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function alerts(): JsonResponse
    {
        $query = new GetFavoritesAlertsQuery(userId: auth()->id());
        $alerts = $this->getFavoritesAlertsHandler->handle($query);

        return ApiResponse::success($alerts->values(), 'Alertes récupérées avec succès');
    }
}



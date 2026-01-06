<?php

namespace App\Presentation\Http\Controllers\Api\V1\Heritage;

use App\Application\Heritage\Commands\FavoriteHeritageStoryCommand;
use App\Application\Heritage\Handlers\FavoriteHeritageStoryHandler;
use App\Application\Heritage\Handlers\GetHeritageStoriesByRegionHandler;
use App\Application\Heritage\Handlers\GetHeritageStoriesHandler;
use App\Application\Heritage\Handlers\GetHeritageStoryHandler;
use App\Application\Heritage\Queries\GetHeritageStoriesByRegionQuery;
use App\Application\Heritage\Queries\GetHeritageStoriesQuery;
use App\Application\Heritage\Queries\GetHeritageStoryQuery;
use App\Domain\Content\Models\HeritageStory;
use App\Presentation\Http\Controllers\Api\V1\Auth\BaseController;
use App\Presentation\Http\Resources\HeritageStoryResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Histoires de nos anciens",
    description: "Endpoints pour consulter les histoires du patrimoine sénégalais"
)]
class HeritageController extends BaseController
{
    public function __construct(
        private GetHeritageStoriesHandler $getHeritageStoriesHandler,
        private GetHeritageStoryHandler $getHeritageStoryHandler,
        private GetHeritageStoriesByRegionHandler $getHeritageStoriesByRegionHandler,
        private FavoriteHeritageStoryHandler $favoriteHeritageStoryHandler
    ) {}

    /**
     * Lister les histoires disponibles
     */
    #[OA\Get(
        path: '/api/v1/heritage/stories',
        summary: 'Lister les histoires disponibles',
        description: 'Récupère la liste paginée de toutes les histoires du patrimoine publiées.',
        tags: ['Histoires de nos anciens'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', description: 'Recherche dans le titre et le contenu', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'is_featured', in: 'query', description: 'Filtrer les histoires mises en avant', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des histoires récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Liste des histoires récupérée avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/HeritageStory')),
                        new OA\Property(property: 'meta', type: 'object'),
                        new OA\Property(property: 'links', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'search' => $request->input('search'),
            'is_featured' => $request->input('is_featured'),
        ], fn($value) => $value !== null);

        $query = new GetHeritageStoriesQuery(
            filters: $filters,
            perPage: (int) $request->input('per_page', 15)
        );

        $stories = $this->getHeritageStoriesHandler->handle($query);

        return ApiResponse::paginated(
            $stories,
            'Liste des histoires récupérée avec succès',
            fn($story) => new HeritageStoryResource($story)
        );
    }

    /**
     * Écouter/lire une histoire
     */
    #[OA\Get(
        path: '/api/v1/heritage/stories/{id}',
        summary: 'Écouter/lire une histoire',
        description: 'Récupère les détails d\'une histoire spécifique du patrimoine.',
        tags: ['Histoires de nos anciens'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID ou UUID de l\'histoire', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Histoire récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Histoire récupérée avec succès'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/HeritageStory'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Histoire non trouvée'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $query = new GetHeritageStoryQuery(storyId: $id);
        $story = $this->getHeritageStoryHandler->handle($query);

        if (!$story) {
            return ApiResponse::error('Histoire non trouvée', 404);
        }

        return ApiResponse::success(
            new HeritageStoryResource($story),
            'Histoire récupérée avec succès'
        );
    }

    /**
     * Sauvegarder une histoire
     */
    #[OA\Post(
        path: '/api/v1/heritage/stories/{id}/favorite',
        summary: 'Sauvegarder une histoire',
        description: 'Ajoute une histoire aux favoris du voyageur authentifié.',
        tags: ['Histoires de nos anciens'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'histoire', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Histoire ajoutée aux favoris',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Histoire ajoutée aux favoris'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Histoire non trouvée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function favorite(int $id): JsonResponse
    {
        $command = new FavoriteHeritageStoryCommand(
            userId: auth()->id(),
            storyId: $id
        );

        try {
            $result = $this->favoriteHeritageStoryHandler->handle($command);
            return ApiResponse::success($result, $result['message']);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Histoires par région
     */
    #[OA\Get(
        path: '/api/v1/heritage/stories/by-region',
        summary: 'Histoires par région',
        description: 'Récupère la liste paginée des histoires filtrées par région.',
        tags: ['Histoires de nos anciens'],
        parameters: [
            new OA\Parameter(name: 'region', in: 'query', required: true, description: 'Nom de la région', schema: new OA\Schema(type: 'string', example: 'Dakar')),
            new OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des histoires par région récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Liste des histoires par région récupérée avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/HeritageStory')),
                        new OA\Property(property: 'meta', type: 'object'),
                        new OA\Property(property: 'links', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function byRegion(Request $request): JsonResponse
    {
        $request->validate([
            'region' => 'required|string|max:100',
        ]);

        $query = new GetHeritageStoriesByRegionQuery(
            region: $request->input('region'),
            perPage: (int) $request->input('per_page', 15)
        );

        $stories = $this->getHeritageStoriesByRegionHandler->handle($query);

        return ApiResponse::paginated(
            $stories,
            'Liste des histoires par région récupérée avec succès',
            fn($story) => new HeritageStoryResource($story)
        );
    }
}


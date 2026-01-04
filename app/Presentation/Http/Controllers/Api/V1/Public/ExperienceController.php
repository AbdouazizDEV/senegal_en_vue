<?php

namespace App\Presentation\Http\Controllers\Api\V1\Public;

use App\Application\Experience\Handlers\CheckAvailabilityHandler;
use App\Application\Experience\Handlers\GetExperienceByIdHandler;
use App\Application\Experience\Handlers\GetExperiencePhotosHandler;
use App\Application\Experience\Handlers\GetExperiencesByPriceHandler;
use App\Application\Experience\Handlers\GetExperiencesByRegionHandler;
use App\Application\Experience\Handlers\GetExperiencesByThemeHandler;
use App\Application\Experience\Handlers\GetFeaturedExperiencesHandler;
use App\Application\Experience\Handlers\GetRecentExperiencesHandler;
use App\Application\Experience\Handlers\GetSimilarExperiencesHandler;
use App\Application\Experience\Handlers\SearchExperiencesHandler;
use App\Application\Experience\Queries\CheckAvailabilityQuery;
use App\Application\Experience\Queries\GetExperienceByIdQuery;
use App\Application\Experience\Queries\GetExperiencePhotosQuery;
use App\Application\Experience\Queries\GetExperiencesByPriceQuery;
use App\Application\Experience\Queries\GetExperiencesByRegionQuery;
use App\Application\Experience\Queries\GetExperiencesByThemeQuery;
use App\Application\Experience\Queries\GetFeaturedExperiencesQuery;
use App\Application\Experience\Queries\GetRecentExperiencesQuery;
use App\Application\Experience\Queries\GetSimilarExperiencesQuery;
use App\Application\Experience\Queries\SearchExperiencesQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Resources\ExperienceResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Voyageur", description: "Endpoints pour les voyageurs")]
class ExperienceController extends Controller
{
    public function __construct(
        private SearchExperiencesHandler $searchExperiencesHandler,
        private GetExperienceByIdHandler $getExperienceByIdHandler,
        private CheckAvailabilityHandler $checkAvailabilityHandler,
        private GetFeaturedExperiencesHandler $getFeaturedExperiencesHandler,
        private GetRecentExperiencesHandler $getRecentExperiencesHandler,
        private GetExperiencesByRegionHandler $getExperiencesByRegionHandler,
        private GetExperiencesByThemeHandler $getExperiencesByThemeHandler,
        private GetExperiencesByPriceHandler $getExperiencesByPriceHandler,
        private GetExperiencePhotosHandler $getExperiencePhotosHandler,
        private GetSimilarExperiencesHandler $getSimilarExperiencesHandler,
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    /**
     * Rechercher des expériences avec filtres
     */
    #[OA\Get(
        path: '/api/v1/experiences',
        summary: 'Rechercher des expériences avec filtres',
        description: 'Recherche des expériences approuvées avec différents filtres (type, région, prix, tags)',
        tags: ['Voyageur'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Recherche textuelle'),
            new OA\Parameter(name: 'type', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['activity', 'tour', 'workshop', 'event', 'accommodation', 'restaurant'])),
            new OA\Parameter(name: 'region', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'Dakar'),
            new OA\Parameter(name: 'city', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'Dakar'),
            new OA\Parameter(name: 'min_price', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'max_price', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'tags', in: 'query', required: false, schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')), style: 'form', explode: true),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des expériences'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = new SearchExperiencesQuery(
            search: $request->input('search'),
            type: $request->input('type'),
            region: $request->input('region'),
            city: $request->input('city'),
            minPrice: $request->input('min_price') ? (float) $request->input('min_price') : null,
            maxPrice: $request->input('max_price') ? (float) $request->input('max_price') : null,
            tags: $request->input('tags'),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $experiences = $this->searchExperiencesHandler->handle($query);

        return ApiResponse::paginated(
            $experiences,
            'Expériences récupérées avec succès',
            fn($exp) => new ExperienceResource($exp)
        );
    }

    /**
     * Recherche avancée avec critères complexes
     */
    #[OA\Post(
        path: '/api/v1/experiences/search',
        summary: 'Recherche avancée avec critères complexes',
        description: 'Recherche avancée avec plusieurs critères combinés',
        tags: ['Voyageur'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'search', type: 'string', nullable: true),
                    new OA\Property(property: 'type', type: 'string', nullable: true),
                    new OA\Property(property: 'region', type: 'string', nullable: true),
                    new OA\Property(property: 'city', type: 'string', nullable: true),
                    new OA\Property(property: 'min_price', type: 'number', format: 'float', nullable: true),
                    new OA\Property(property: 'max_price', type: 'number', format: 'float', nullable: true),
                    new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
                    new OA\Property(property: 'page', type: 'integer', default: 1),
                    new OA\Property(property: 'per_page', type: 'integer', default: 15),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Résultats de la recherche'),
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        $query = new SearchExperiencesQuery(
            search: $request->input('search'),
            type: $request->input('type'),
            region: $request->input('region'),
            city: $request->input('city'),
            minPrice: $request->input('min_price') ? (float) $request->input('min_price') : null,
            maxPrice: $request->input('max_price') ? (float) $request->input('max_price') : null,
            tags: $request->input('tags'),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $experiences = $this->searchExperiencesHandler->handle($query);

        return ApiResponse::paginated(
            $experiences,
            'Résultats de la recherche',
            fn($exp) => new ExperienceResource($exp)
        );
    }

    /**
     * Consulter les détails d'une expérience
     */
    #[OA\Get(
        path: '/api/v1/experiences/{id}',
        summary: 'Consulter les détails d\'une expérience',
        description: 'Récupère les détails complets d\'une expérience approuvée',
        tags: ['Voyageur'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails de l\'expérience'),
            new OA\Response(response: 404, description: 'Expérience non trouvée'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $query = new GetExperienceByIdQuery(experienceId: (int) $id);
        $experience = $this->getExperienceByIdHandler->handle($query);

        if (!$experience || $experience->status !== \App\Domain\Experience\Enums\ExperienceStatus::APPROVED) {
            return ApiResponse::error('Expérience non trouvée', 404);
        }

        return ApiResponse::success(new ExperienceResource($experience), 'Expérience récupérée avec succès');
    }

    /**
     * Vérifier la disponibilité
     */
    #[OA\Get(
        path: '/api/v1/experiences/{id}/availability',
        summary: 'Vérifier la disponibilité',
        description: 'Vérifie la disponibilité d\'une expérience pour une date et un nombre de participants',
        tags: ['Voyageur'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date'), example: '2026-01-15'),
            new OA\Parameter(name: 'participants', in: 'query', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 2),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Disponibilité vérifiée',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Disponibilité vérifiée'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'available', type: 'boolean', example: true),
                            new OA\Property(property: 'date', type: 'string', format: 'date', example: '2026-01-15'),
                            new OA\Property(property: 'participants', type: 'integer', example: 2),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Expérience non trouvée'),
        ]
    )]
    public function availability(Request $request, string $id): JsonResponse
    {
        // Validation avec messages clairs
        $validated = $request->validate([
            'date' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'participants' => [
                'required',
                'integer',
                'min:1',
            ],
        ], [
            'date.required' => 'La date est obligatoire pour vérifier la disponibilité.',
            'date.date' => 'La date doit être au format valide (YYYY-MM-DD).',
            'date.date_format' => 'La date doit être au format YYYY-MM-DD (exemple : 2026-01-15).',
            'date.after_or_equal' => 'La date doit être aujourd\'hui ou une date future.',
            'participants.required' => 'Le nombre de participants est obligatoire.',
            'participants.integer' => 'Le nombre de participants doit être un nombre entier.',
            'participants.min' => 'Le nombre de participants doit être au moins 1.',
        ]);

        // Vérifier que l'expérience existe et est approuvée
        $experience = $this->experienceRepository->findById((int) $id);
        
        if (!$experience) {
            return ApiResponse::error('Expérience non trouvée.', 404);
        }

        if ($experience->status !== \App\Domain\Experience\Enums\ExperienceStatus::APPROVED) {
            return ApiResponse::error('Cette expérience n\'est pas disponible pour le moment.', 403);
        }

        // Vérifier les limites de participants
        if ($validated['participants'] < $experience->min_participants) {
            return ApiResponse::error(
                "Le nombre minimum de participants requis est {$experience->min_participants}.",
                422
            );
        }

        if ($validated['participants'] > $experience->max_participants) {
            return ApiResponse::error(
                "Le nombre maximum de participants autorisé est {$experience->max_participants}.",
                422
            );
        }

        $query = new CheckAvailabilityQuery(
            experienceId: (int) $id,
            date: $validated['date'],
            participants: $validated['participants']
        );

        $isAvailable = $this->checkAvailabilityHandler->handle($query);
        
        // Calculer les places restantes
        $existingBookings = \App\Domain\Booking\Models\Booking::where('experience_id', (int) $id)
            ->whereDate('booking_date', $validated['date'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->sum('participants_count');
        
        $remainingSlots = max(0, $experience->max_participants - $existingBookings);

        return ApiResponse::success([
            'is_available' => $isAvailable,
            'date' => $validated['date'],
            'participants' => $validated['participants'],
            'remaining_slots' => $remainingSlots,
            'min_participants' => $experience->min_participants,
            'max_participants' => $experience->max_participants,
            'message' => $isAvailable 
                ? "L'expérience est disponible pour {$validated['participants']} participant(s) le {$validated['date']}."
                : "L'expérience n'est pas disponible pour {$validated['participants']} participant(s) le {$validated['date']}.",
        ], $isAvailable ? 'Disponibilité vérifiée avec succès' : 'Expérience non disponible pour cette date et ce nombre de participants');
    }

    /**
     * Expériences mises en avant
     */
    #[OA\Get(
        path: '/api/v1/experiences/featured',
        summary: 'Expériences mises en avant',
        description: 'Récupère les expériences mises en avant',
        tags: ['Voyageur'],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des expériences mises en avant'),
        ]
    )]
    public function featured(Request $request): JsonResponse
    {
        $query = new GetFeaturedExperiencesQuery(limit: (int) $request->input('limit', 10));
        $experiences = $this->getFeaturedExperiencesHandler->handle($query);

        return ApiResponse::success(
            $experiences->map(fn($exp) => new ExperienceResource($exp)),
            'Expériences mises en avant récupérées avec succès'
        );
    }

    /**
     * Nouvelles expériences
     */
    #[OA\Get(
        path: '/api/v1/experiences/recent',
        summary: 'Nouvelles expériences',
        description: 'Récupère les nouvelles expériences',
        tags: ['Voyageur'],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des nouvelles expériences'),
        ]
    )]
    public function recent(Request $request): JsonResponse
    {
        $query = new GetRecentExperiencesQuery(limit: (int) $request->input('limit', 10));
        $experiences = $this->getRecentExperiencesHandler->handle($query);

        return ApiResponse::success(
            $experiences->map(fn($exp) => new ExperienceResource($exp)),
            'Nouvelles expériences récupérées avec succès'
        );
    }

    /**
     * Filtrer par région
     */
    #[OA\Get(
        path: '/api/v1/experiences/by-region',
        summary: 'Filtrer par région',
        description: 'Récupère les expériences filtrées par région',
        tags: ['Voyageur'],
        parameters: [
            new OA\Parameter(name: 'region', in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: 'Dakar'),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des expériences par région'),
        ]
    )]
    public function byRegion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'region' => 'required|string',
        ]);

        $query = new GetExperiencesByRegionQuery(
            region: $validated['region'],
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $experiences = $this->getExperiencesByRegionHandler->handle($query);

        return ApiResponse::paginated(
            $experiences,
            'Expériences par région récupérées avec succès',
            fn($exp) => new ExperienceResource($exp)
        );
    }

    /**
     * Filtrer par thématique
     */
    #[OA\Get(
        path: '/api/v1/experiences/by-theme',
        summary: 'Filtrer par thématique',
        description: 'Récupère les expériences filtrées par thématique (tag)',
        tags: ['Voyageur'],
        parameters: [
            new OA\Parameter(name: 'theme', in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: 'culture'),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des expériences par thématique'),
        ]
    )]
    public function byTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme' => 'required|string',
        ]);

        $query = new GetExperiencesByThemeQuery(
            theme: $validated['theme'],
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $experiences = $this->getExperiencesByThemeHandler->handle($query);

        return ApiResponse::paginated(
            $experiences,
            'Expériences par thématique récupérées avec succès',
            fn($exp) => new ExperienceResource($exp)
        );
    }

    /**
     * Filtrer par prix
     */
    #[OA\Get(
        path: '/api/v1/experiences/by-price',
        summary: 'Filtrer par prix',
        description: 'Récupère les expériences filtrées par fourchette de prix',
        tags: ['Voyageur'],
        parameters: [
            new OA\Parameter(name: 'min_price', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float'), example: 5000),
            new OA\Parameter(name: 'max_price', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float'), example: 50000),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des expériences par prix'),
        ]
    )]
    public function byPrice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'min_price' => 'required|numeric|min:0',
            'max_price' => 'required|numeric|min:0|gte:min_price',
        ]);

        $query = new GetExperiencesByPriceQuery(
            minPrice: (float) $validated['min_price'],
            maxPrice: (float) $validated['max_price'],
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $experiences = $this->getExperiencesByPriceHandler->handle($query);

        return ApiResponse::paginated(
            $experiences,
            'Expériences par prix récupérées avec succès',
            fn($exp) => new ExperienceResource($exp)
        );
    }

    /**
     * Galerie photos d'une expérience
     */
    #[OA\Get(
        path: '/api/v1/experiences/{id}/photos',
        summary: 'Galerie photos d\'une expérience',
        description: 'Récupère toutes les photos d\'une expérience',
        tags: ['Voyageur'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Photos récupérées avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Photos récupérées avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'string', format: 'uri')),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Expérience non trouvée'),
        ]
    )]
    public function photos(string $id): JsonResponse
    {
        $query = new GetExperiencePhotosQuery(experienceId: (int) $id);
        $photos = $this->getExperiencePhotosHandler->handle($query);

        return ApiResponse::success($photos, 'Photos récupérées avec succès');
    }

    /**
     * Expériences similaires
     */
    #[OA\Get(
        path: '/api/v1/experiences/{id}/similar',
        summary: 'Expériences similaires',
        description: 'Récupère les expériences similaires à une expérience donnée',
        tags: ['Voyageur'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 5)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des expériences similaires'),
        ]
    )]
    public function similar(Request $request, string $id): JsonResponse
    {
        $query = new GetSimilarExperiencesQuery(
            experienceId: (int) $id,
            limit: (int) $request->input('limit', 5)
        );

        $experiences = $this->getSimilarExperiencesHandler->handle($query);

        return ApiResponse::success(
            $experiences->map(fn($exp) => new ExperienceResource($exp)),
            'Expériences similaires récupérées avec succès'
        );
    }
}


<?php

namespace App\Presentation\Http\Controllers\Api\V1\Traveler;

use App\Application\Traveler\TravelBook\Commands\AddPhotosCommand;
use App\Application\Traveler\TravelBook\Commands\CreateEntryCommand;
use App\Application\Traveler\TravelBook\Commands\DeleteEntryCommand;
use App\Application\Traveler\TravelBook\Commands\ShareTravelBookCommand;
use App\Application\Traveler\TravelBook\Commands\UpdateEntryCommand;
use App\Application\Traveler\TravelBook\Handlers\AddPhotosHandler;
use App\Application\Traveler\TravelBook\Handlers\CreateEntryHandler;
use App\Application\Traveler\TravelBook\Handlers\DeleteEntryHandler;
use App\Application\Traveler\TravelBook\Handlers\GetEntryHandler;
use App\Application\Traveler\TravelBook\Handlers\GetTravelBookHandler;
use App\Application\Traveler\TravelBook\Handlers\ShareTravelBookHandler;
use App\Application\Traveler\TravelBook\Handlers\UpdateEntryHandler;
use App\Application\Traveler\TravelBook\Queries\GetEntryQuery;
use App\Application\Traveler\TravelBook\Queries\GetTravelBookQuery;
use App\Infrastructure\Repositories\Contracts\TravelBookRepositoryInterface;
use App\Presentation\Http\Controllers\Api\V1\Auth\BaseController;
use App\Presentation\Http\Requests\Traveler\TravelBook\AddPhotosRequest;
use App\Presentation\Http\Requests\Traveler\TravelBook\CreateEntryRequest;
use App\Presentation\Http\Requests\Traveler\TravelBook\ShareTravelBookRequest;
use App\Presentation\Http\Requests\Traveler\TravelBook\UpdateEntryRequest;
use App\Presentation\Http\Resources\TravelBookEntryResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Voyageur - Carnet de voyage",
    description: "Endpoints pour la gestion du carnet de voyage par les voyageurs"
)]
class TravelBookController extends BaseController
{
    public function __construct(
        private GetTravelBookHandler $getTravelBookHandler,
        private GetEntryHandler $getEntryHandler,
        private CreateEntryHandler $createEntryHandler,
        private UpdateEntryHandler $updateEntryHandler,
        private DeleteEntryHandler $deleteEntryHandler,
        private AddPhotosHandler $addPhotosHandler,
        private ShareTravelBookHandler $shareTravelBookHandler,
        private TravelBookRepositoryInterface $travelBookRepository
    ) {}

    /**
     * Mon carnet de voyage
     */
    #[OA\Get(
        path: '/api/v1/traveler/travelbook',
        summary: 'Consulter mon carnet de voyage',
        description: 'Récupère la liste paginée de toutes les entrées du carnet de voyage du voyageur authentifié avec filtres optionnels.',
        tags: ['Voyageur - Carnet de voyage'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', description: 'Recherche dans le titre et le contenu', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'experience_id', in: 'query', description: 'Filtrer par expérience', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'visibility', in: 'query', description: 'Filtrer par visibilité', required: false, schema: new OA\Schema(type: 'string', enum: ['private', 'friends', 'public'])),
            new OA\Parameter(name: 'start_date', in: 'query', description: 'Date de début (YYYY-MM-DD)', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'end_date', in: 'query', description: 'Date de fin (YYYY-MM-DD)', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Carnet de voyage récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Carnet de voyage récupéré avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/TravelBookEntry')),
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
        $filters = array_filter([
            'search' => $request->input('search'),
            'experience_id' => $request->input('experience_id'),
            'visibility' => $request->input('visibility'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ], fn($value) => $value !== null);

        $query = new GetTravelBookQuery(
            travelerId: auth()->id(),
            filters: $filters,
            perPage: (int) $request->input('per_page', 15)
        );

        $entries = $this->getTravelBookHandler->handle($query);

        return ApiResponse::paginated(
            $entries,
            'Carnet de voyage récupéré avec succès',
            fn($entry) => new TravelBookEntryResource($entry)
        );
    }

    /**
     * Ajouter une entrée
     */
    #[OA\Post(
        path: '/api/v1/traveler/travelbook/entries',
        summary: 'Ajouter une entrée au carnet de voyage',
        description: 'Crée une nouvelle entrée dans le carnet de voyage du voyageur authentifié.',
        tags: ['Voyageur - Carnet de voyage'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'content', 'entry_date'],
                properties: [
                    new OA\Property(property: 'experience_id', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'booking_id', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'title', type: 'string', example: 'Ma journée à Dakar'),
                    new OA\Property(property: 'content', type: 'string', example: 'J\'ai passé une merveilleuse journée...'),
                    new OA\Property(property: 'entry_date', type: 'string', format: 'date', example: '2026-03-15'),
                    new OA\Property(property: 'location', type: 'string', nullable: true, example: 'Dakar, Sénégal'),
                    new OA\Property(property: 'location_details', type: 'object', nullable: true),
                    new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string'), nullable: true, example: ['culture', 'histoire']),
                    new OA\Property(property: 'visibility', type: 'string', enum: ['private', 'friends', 'public'], example: 'private'),
                    new OA\Property(property: 'metadata', type: 'object', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Entrée créée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Entrée créée avec succès'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/TravelBookEntry'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function store(CreateEntryRequest $request): JsonResponse
    {
        $command = new CreateEntryCommand(
            travelerId: auth()->id(),
            experienceId: $request->input('experience_id'),
            bookingId: $request->input('booking_id'),
            title: $request->input('title'),
            content: $request->input('content'),
            entryDate: $request->input('entry_date'),
            location: $request->input('location'),
            locationDetails: $request->input('location_details'),
            tags: $request->input('tags'),
            visibility: $request->input('visibility', 'private'),
            metadata: $request->input('metadata')
        );

        $entry = $this->createEntryHandler->handle($command);

        return ApiResponse::success(
            new TravelBookEntryResource($entry->load(['experience', 'booking', 'traveler'])),
            'Entrée créée avec succès',
            201
        );
    }

    /**
     * Consulter une entrée
     */
    #[OA\Get(
        path: '/api/v1/traveler/travelbook/entries/{id}',
        summary: 'Consulter une entrée du carnet',
        description: 'Récupère les détails d\'une entrée spécifique du carnet de voyage.',
        tags: ['Voyageur - Carnet de voyage'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID ou UUID de l\'entrée', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Entrée récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Entrée récupérée avec succès'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/TravelBookEntry'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Entrée non trouvée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $query = new GetEntryQuery(
            travelerId: auth()->id(),
            entryId: $id
        );

        $entry = $this->getEntryHandler->handle($query);

        if (!$entry) {
            return ApiResponse::error('Entrée non trouvée', 404);
        }

        return ApiResponse::success(
            new TravelBookEntryResource($entry),
            'Entrée récupérée avec succès'
        );
    }

    /**
     * Modifier une entrée
     */
    #[OA\Put(
        path: '/api/v1/traveler/travelbook/entries/{id}',
        summary: 'Modifier une entrée du carnet',
        description: 'Met à jour une entrée existante du carnet de voyage.',
        tags: ['Voyageur - Carnet de voyage'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'entrée', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Ma journée à Dakar (modifié)'),
                    new OA\Property(property: 'content', type: 'string', example: 'J\'ai passé une merveilleuse journée...'),
                    new OA\Property(property: 'entry_date', type: 'string', format: 'date', example: '2026-03-15'),
                    new OA\Property(property: 'location', type: 'string', nullable: true),
                    new OA\Property(property: 'location_details', type: 'object', nullable: true),
                    new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
                    new OA\Property(property: 'visibility', type: 'string', enum: ['private', 'friends', 'public'], nullable: true),
                    new OA\Property(property: 'metadata', type: 'object', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Entrée modifiée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Entrée modifiée avec succès'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/TravelBookEntry'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Entrée non trouvée'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function update(UpdateEntryRequest $request, int $id): JsonResponse
    {
        $command = new UpdateEntryCommand(
            travelerId: auth()->id(),
            entryId: $id,
            title: $request->input('title'),
            content: $request->input('content'),
            entryDate: $request->input('entry_date'),
            location: $request->input('location'),
            locationDetails: $request->input('location_details'),
            tags: $request->input('tags'),
            visibility: $request->input('visibility'),
            metadata: $request->input('metadata')
        );

        try {
            $entry = $this->updateEntryHandler->handle($command);
            return ApiResponse::success(
                new TravelBookEntryResource($entry),
                'Entrée modifiée avec succès'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Supprimer une entrée
     */
    #[OA\Delete(
        path: '/api/v1/traveler/travelbook/entries/{id}',
        summary: 'Supprimer une entrée du carnet',
        description: 'Supprime une entrée du carnet de voyage.',
        tags: ['Voyageur - Carnet de voyage'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'entrée', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Entrée supprimée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Entrée supprimée avec succès'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Entrée non trouvée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $command = new DeleteEntryCommand(
            travelerId: auth()->id(),
            entryId: $id
        );

        try {
            $this->deleteEntryHandler->handle($command);
            return ApiResponse::success(null, 'Entrée supprimée avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Ajouter des photos
     */
    #[OA\Post(
        path: '/api/v1/traveler/travelbook/entries/{id}/photos',
        summary: 'Ajouter des photos à une entrée',
        description: 'Ajoute des photos à une entrée existante du carnet de voyage.',
        tags: ['Voyageur - Carnet de voyage'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'entrée', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['photos'],
                properties: [
                    new OA\Property(property: 'photos', type: 'array', items: new OA\Items(type: 'string'), example: ['https://res.cloudinary.com/.../photo1.jpg', 'https://res.cloudinary.com/.../photo2.jpg']),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Photos ajoutées avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Photos ajoutées avec succès'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/TravelBookEntry'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Entrée non trouvée'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function addPhotos(AddPhotosRequest $request, int $id): JsonResponse
    {
        $command = new AddPhotosCommand(
            travelerId: auth()->id(),
            entryId: $id,
            photoUrls: $request->input('photos')
        );

        try {
            $entry = $this->addPhotosHandler->handle($command);
            return ApiResponse::success(
                new TravelBookEntryResource($entry),
                'Photos ajoutées avec succès'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Partager mon carnet
     */
    #[OA\Post(
        path: '/api/v1/traveler/travelbook/share',
        summary: 'Partager mon carnet de voyage',
        description: 'Change la visibilité du carnet de voyage ou d\'entrées spécifiques.',
        tags: ['Voyageur - Carnet de voyage'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['visibility'],
                properties: [
                    new OA\Property(property: 'visibility', type: 'string', enum: ['friends', 'public'], example: 'public'),
                    new OA\Property(property: 'entry_ids', type: 'array', items: new OA\Items(type: 'integer'), nullable: true, description: 'IDs des entrées à partager (si vide, partage tout le carnet)'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Carnet partagé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Carnet partagé avec succès'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function share(ShareTravelBookRequest $request): JsonResponse
    {
        $command = new ShareTravelBookCommand(
            travelerId: auth()->id(),
            visibility: $request->input('visibility'),
            entryIds: $request->input('entry_ids')
        );

        $result = $this->shareTravelBookHandler->handle($command);

        return ApiResponse::success($result, $result['message']);
    }

    /**
     * Exporter mon carnet (PDF)
     */
    #[OA\Get(
        path: '/api/v1/traveler/travelbook/export',
        summary: 'Exporter mon carnet de voyage en PDF',
        description: 'Génère et télécharge un fichier PDF contenant toutes les entrées du carnet de voyage.',
        tags: ['Voyageur - Carnet de voyage'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'start_date', in: 'query', description: 'Date de début (YYYY-MM-DD)', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'end_date', in: 'query', description: 'Date de fin (YYYY-MM-DD)', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'PDF généré avec succès',
                content: new OA\MediaType(
                    mediaType: 'application/pdf',
                    schema: new OA\Schema(type: 'string', format: 'binary')
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate) {
            $startDate = now()->subYear()->format('Y-m-d');
        }

        if (!$endDate) {
            $endDate = now()->format('Y-m-d');
        }

        $entries = $this->travelBookRepository->getByDateRange(
            auth()->id(),
            $startDate,
            $endDate
        );

        // TODO: Implémenter la génération PDF avec une bibliothèque comme dompdf ou barryvdh/laravel-dompdf
        // Pour l'instant, retourner un JSON avec les données
        return ApiResponse::success(
            TravelBookEntryResource::collection($entries),
            'Export généré avec succès (PDF à implémenter)'
        );
    }
}



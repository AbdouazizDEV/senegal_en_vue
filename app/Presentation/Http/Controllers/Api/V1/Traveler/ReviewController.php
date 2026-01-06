<?php

namespace App\Presentation\Http\Controllers\Api\V1\Traveler;

use App\Application\Traveler\Review\Commands\CreateReviewCommand;
use App\Application\Traveler\Review\Commands\DeleteReviewCommand;
use App\Application\Traveler\Review\Commands\MarkHelpfulCommand;
use App\Application\Traveler\Review\Commands\UpdateReviewCommand;
use App\Application\Traveler\Review\Handlers\CreateReviewHandler;
use App\Application\Traveler\Review\Handlers\DeleteReviewHandler;
use App\Application\Traveler\Review\Handlers\GetExperienceReviewsHandler;
use App\Application\Traveler\Review\Handlers\GetReviewsHandler;
use App\Application\Traveler\Review\Handlers\MarkHelpfulHandler;
use App\Application\Traveler\Review\Handlers\UpdateReviewHandler;
use App\Application\Traveler\Review\Queries\GetExperienceReviewsQuery;
use App\Application\Traveler\Review\Queries\GetReviewsQuery;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;
use App\Presentation\Http\Controllers\Api\V1\Auth\BaseController;
use App\Presentation\Http\Requests\Traveler\Review\CreateReviewRequest;
use App\Presentation\Http\Requests\Traveler\Review\UpdateReviewRequest;
use App\Presentation\Http\Resources\ReviewResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Voyageur - Avis et évaluations",
    description: "Endpoints pour la gestion des avis et évaluations par les voyageurs"
)]
class ReviewController extends BaseController
{
    public function __construct(
        private GetReviewsHandler $getReviewsHandler,
        private GetExperienceReviewsHandler $getExperienceReviewsHandler,
        private CreateReviewHandler $createReviewHandler,
        private UpdateReviewHandler $updateReviewHandler,
        private DeleteReviewHandler $deleteReviewHandler,
        private MarkHelpfulHandler $markHelpfulHandler,
        private BookingRepositoryInterface $bookingRepository,
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    /**
     * Mes avis laissés
     */
    #[OA\Get(
        path: '/api/v1/traveler/reviews',
        summary: 'Mes avis laissés',
        description: 'Récupère la liste paginée de tous les avis laissés par le voyageur authentifié.',
        tags: ['Voyageur - Avis et évaluations'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', description: 'Filtrer par statut', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'approved', 'rejected', 'reported'])),
            new OA\Parameter(name: 'experience_id', in: 'query', description: 'Filtrer par expérience', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'rating', in: 'query', description: 'Filtrer par note (1-5)', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 5)),
            new OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des avis récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Liste des avis récupérée avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Review')),
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
            'status' => $request->input('status'),
            'experience_id' => $request->input('experience_id'),
            'rating' => $request->input('rating'),
        ], fn($value) => $value !== null);

        $query = new GetReviewsQuery(
            travelerId: auth()->id(),
            filters: $filters,
            perPage: (int) $request->input('per_page', 15)
        );

        $reviews = $this->getReviewsHandler->handle($query);

        return ApiResponse::paginated(
            $reviews,
            'Liste des avis récupérée avec succès',
            fn($review) => new ReviewResource($review)
        );
    }

    /**
     * Laisser un avis sur une expérience
     */
    #[OA\Post(
        path: '/api/v1/traveler/reviews',
        summary: 'Laisser un avis sur une expérience',
        description: 'Crée un nouvel avis pour une expérience basée sur une réservation.',
        tags: ['Voyageur - Avis et évaluations'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['booking_id', 'rating', 'comment'],
                properties: [
                    new OA\Property(property: 'booking_id', type: 'integer', example: 1),
                    new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, example: 5),
                    new OA\Property(property: 'title', type: 'string', nullable: true, example: 'Expérience exceptionnelle'),
                    new OA\Property(property: 'comment', type: 'string', example: 'J\'ai passé un moment inoubliable...'),
                    new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Avis créé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Avis créé avec succès'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Review'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function store(CreateReviewRequest $request): JsonResponse
    {
        $booking = $this->bookingRepository->findById($request->input('booking_id'));

        if (!$booking || $booking->traveler_id !== auth()->id()) {
            return ApiResponse::error('Réservation non trouvée ou accès non autorisé', 404);
        }

        $command = new CreateReviewCommand(
            travelerId: auth()->id(),
            bookingId: $request->input('booking_id'),
            experienceId: $booking->experience_id,
            providerId: $booking->provider_id,
            rating: $request->input('rating'),
            comment: $request->input('comment'),
            title: $request->input('title'),
            images: $request->input('images')
        );

        try {
            $review = $this->createReviewHandler->handle($command);
            return ApiResponse::success(
                new ReviewResource($review->load(['experience', 'booking', 'provider'])),
                'Avis créé avec succès',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Modifier mon avis
     */
    #[OA\Put(
        path: '/api/v1/traveler/reviews/{id}',
        summary: 'Modifier mon avis',
        description: 'Met à jour un avis existant laissé par le voyageur.',
        tags: ['Voyageur - Avis et évaluations'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'avis', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, nullable: true),
                    new OA\Property(property: 'title', type: 'string', nullable: true),
                    new OA\Property(property: 'comment', type: 'string', nullable: true),
                    new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Avis modifié avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Avis modifié avec succès'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Review'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Avis non trouvé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function update(UpdateReviewRequest $request, int $id): JsonResponse
    {
        $command = new UpdateReviewCommand(
            travelerId: auth()->id(),
            reviewId: $id,
            rating: $request->input('rating'),
            title: $request->input('title'),
            comment: $request->input('comment'),
            images: $request->input('images')
        );

        try {
            $review = $this->updateReviewHandler->handle($command);
            return ApiResponse::success(
                new ReviewResource($review),
                'Avis modifié avec succès'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Supprimer mon avis
     */
    #[OA\Delete(
        path: '/api/v1/traveler/reviews/{id}',
        summary: 'Supprimer mon avis',
        description: 'Supprime un avis laissé par le voyageur.',
        tags: ['Voyageur - Avis et évaluations'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'avis', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Avis supprimé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Avis supprimé avec succès'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Avis non trouvé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $command = new DeleteReviewCommand(
            travelerId: auth()->id(),
            reviewId: $id
        );

        try {
            $this->deleteReviewHandler->handle($command);
            return ApiResponse::success(null, 'Avis supprimé avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Consulter les avis d'une expérience
     */
    #[OA\Get(
        path: '/api/v1/experiences/{id}/reviews',
        summary: 'Consulter les avis d\'une expérience',
        description: 'Récupère la liste paginée de tous les avis approuvés pour une expérience spécifique.',
        tags: ['Voyageur - Avis et évaluations'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'expérience', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'rating', in: 'query', description: 'Filtrer par note (1-5)', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 5)),
            new OA\Parameter(name: 'is_verified', in: 'query', description: 'Filtrer par avis vérifiés', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'order_by', in: 'query', description: 'Trier par', required: false, schema: new OA\Schema(type: 'string', enum: ['created_at', 'rating', 'helpful_count'], default: 'created_at')),
            new OA\Parameter(name: 'order_direction', in: 'query', description: 'Direction du tri', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')),
            new OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des avis récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Liste des avis récupérée avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Review')),
                        new OA\Property(property: 'meta', type: 'object'),
                        new OA\Property(property: 'links', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Expérience non trouvée'),
        ]
    )]
    public function getExperienceReviews(Request $request, int $id): JsonResponse
    {
        $experience = $this->experienceRepository->findById($id);

        if (!$experience) {
            return ApiResponse::error('Expérience non trouvée', 404);
        }

        $filters = array_filter([
            'rating' => $request->input('rating'),
            'is_verified' => $request->input('is_verified'),
            'order_by' => $request->input('order_by', 'created_at'),
            'order_direction' => $request->input('order_direction', 'desc'),
        ], fn($value) => $value !== null);

        $query = new GetExperienceReviewsQuery(
            experienceId: $id,
            filters: $filters,
            perPage: (int) $request->input('per_page', 15)
        );

        $reviews = $this->getExperienceReviewsHandler->handle($query);

        return ApiResponse::paginated(
            $reviews,
            'Liste des avis récupérée avec succès',
            fn($review) => new ReviewResource($review)
        );
    }

    /**
     * Marquer un avis comme utile
     */
    #[OA\Post(
        path: '/api/v1/traveler/reviews/{id}/helpful',
        summary: 'Marquer un avis comme utile',
        description: 'Incrémente le compteur "utile" d\'un avis.',
        tags: ['Voyageur - Avis et évaluations'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'avis', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Avis marqué comme utile',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Avis marqué comme utile'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Review'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Avis non trouvé'),
        ]
    )]
    public function markHelpful(int $id): JsonResponse
    {
        $command = new MarkHelpfulCommand(reviewId: $id);

        try {
            $review = $this->markHelpfulHandler->handle($command);
            return ApiResponse::success(
                new ReviewResource($review),
                'Avis marqué comme utile'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }
}


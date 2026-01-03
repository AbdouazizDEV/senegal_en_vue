<?php

namespace App\Presentation\Http\Controllers\Api\V1\Admin;

use App\Application\Review\Commands\DeleteReviewCommand;
use App\Application\Review\Commands\ModerateReviewCommand;
use App\Application\Review\Handlers\DeleteReviewHandler;
use App\Application\Review\Handlers\GetAllReviewsHandler;
use App\Application\Review\Handlers\GetReportedReviewsHandler;
use App\Application\Review\Handlers\GetReviewByIdHandler;
use App\Application\Review\Handlers\GetReviewStatisticsHandler;
use App\Application\Review\Handlers\ModerateReviewHandler;
use App\Application\Review\Queries\GetAllReviewsQuery;
use App\Application\Review\Queries\GetReportedReviewsQuery;
use App\Application\Review\Queries\GetReviewByIdQuery;
use App\Application\Review\Queries\GetReviewStatisticsQuery;
use App\Domain\Review\Enums\ReviewStatus;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Resources\ReviewResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ReviewController extends Controller
{
    public function __construct(
        private GetAllReviewsHandler $getAllReviewsHandler,
        private GetReviewByIdHandler $getReviewByIdHandler,
        private GetReportedReviewsHandler $getReportedReviewsHandler,
        private GetReviewStatisticsHandler $getReviewStatisticsHandler,
        private ModerateReviewHandler $moderateReviewHandler,
        private DeleteReviewHandler $deleteReviewHandler,
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    /**
     * Lister tous les avis
     */
    #[OA\Get(
        path: '/api/v1/admin/reviews',
        summary: 'Lister tous les avis',
        description: 'Récupère la liste paginée de tous les avis avec filtres optionnels',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'approved', 'rejected', 'reported'])),
            new OA\Parameter(name: 'experience_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'provider_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'rating', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 5)),
            new OA\Parameter(name: 'is_verified', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des avis'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = new GetAllReviewsQuery(
            status: $request->input('status'),
            experienceId: $request->input('experience_id') ? (int) $request->input('experience_id') : null,
            providerId: $request->input('provider_id') ? (int) $request->input('provider_id') : null,
            rating: $request->input('rating') ? (int) $request->input('rating') : null,
            isVerified: $request->input('is_verified') !== null ? (bool) $request->input('is_verified') : null,
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $reviews = $this->getAllReviewsHandler->handle($query);

        return ApiResponse::paginated(
            $reviews,
            'Liste des avis récupérée avec succès',
            fn($review) => new ReviewResource($review)
        );
    }

    /**
     * Avis signalés
     */
    #[OA\Get(
        path: '/api/v1/admin/reviews/reported',
        summary: 'Avis signalés',
        description: 'Récupère la liste des avis signalés par les utilisateurs',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des avis signalés'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function reported(Request $request): JsonResponse
    {
        $query = new GetReportedReviewsQuery(
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $reviews = $this->getReportedReviewsHandler->handle($query);

        return ApiResponse::paginated(
            $reviews,
            'Liste des avis signalés récupérée avec succès',
            fn($review) => new ReviewResource($review)
        );
    }

    /**
     * Modérer un avis
     */
    #[OA\Put(
        path: '/api/v1/admin/reviews/{id}/moderate',
        summary: 'Modérer un avis',
        description: 'Modère un avis (approuver/rejeter)',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['approved', 'rejected'], example: 'approved'),
                    new OA\Property(property: 'reason', type: 'string', nullable: true, example: 'Avis conforme'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Avis modéré avec succès'),
            new OA\Response(response: 404, description: 'Avis non trouvé'),
            new OA\Response(response: 400, description: 'Statut invalide'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function moderate(Request $request, string $id): JsonResponse
    {
        try {
            $status = ReviewStatus::from($request->input('status'));
            
            if (!in_array($status, [ReviewStatus::APPROVED, ReviewStatus::REJECTED])) {
                return ApiResponse::error('Statut invalide pour la modération', 400);
            }

            $command = new ModerateReviewCommand(
                reviewId: (int) $id,
                status: $status,
                reason: $request->input('reason')
            );
            $review = $this->moderateReviewHandler->handle($command);

            return ApiResponse::success(new ReviewResource($review), 'Avis modéré avec succès');
        } catch (\ValueError $e) {
            return ApiResponse::error('Statut invalide', 400);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Supprimer un avis inapproprié
     */
    #[OA\Delete(
        path: '/api/v1/admin/reviews/{id}',
        summary: 'Supprimer un avis inapproprié',
        description: 'Supprime définitivement un avis inapproprié',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Avis supprimé avec succès'),
            new OA\Response(response: 404, description: 'Avis non trouvé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $command = new DeleteReviewCommand(reviewId: (int) $id);
        $deleted = $this->deleteReviewHandler->handle($command);

        if (!$deleted) {
            return ApiResponse::error('Avis non trouvé', 404);
        }

        return ApiResponse::success(null, 'Avis supprimé avec succès');
    }

    /**
     * Statistiques des avis
     */
    #[OA\Get(
        path: '/api/v1/admin/reviews/statistics',
        summary: 'Statistiques des avis',
        description: 'Récupère les statistiques des avis',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistiques des avis',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total', type: 'integer', example: 150),
                                new OA\Property(property: 'approved', type: 'integer', example: 120),
                                new OA\Property(property: 'reported', type: 'integer', example: 10),
                                new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.5),
                                new OA\Property(
                                    property: 'by_rating',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: '5', type: 'integer', example: 50),
                                        new OA\Property(property: '4', type: 'integer', example: 40),
                                        new OA\Property(property: '3', type: 'integer', example: 20),
                                    ]
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function statistics(): JsonResponse
    {
        $query = new GetReviewStatisticsQuery();
        $statistics = $this->getReviewStatisticsHandler->handle($query);

        return ApiResponse::success($statistics, 'Statistiques récupérées avec succès');
    }
}


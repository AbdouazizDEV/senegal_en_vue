<?php

namespace App\Presentation\Http\Controllers\Api\V1\Admin;

use App\Application\Booking\Commands\CancelBookingCommand;
use App\Application\Booking\Commands\UpdateBookingStatusCommand;
use App\Application\Booking\Handlers\CancelBookingHandler;
use App\Application\Booking\Handlers\GetAllBookingsHandler;
use App\Application\Booking\Handlers\GetBookingByIdHandler;
use App\Application\Booking\Handlers\GetBookingDisputesHandler;
use App\Application\Booking\Handlers\GetBookingStatisticsHandler;
use App\Application\Booking\Handlers\UpdateBookingStatusHandler;
use App\Application\Booking\Queries\GetAllBookingsQuery;
use App\Application\Booking\Queries\GetBookingByIdQuery;
use App\Application\Booking\Queries\GetBookingDisputesQuery;
use App\Application\Booking\Queries\GetBookingStatisticsQuery;
use App\Domain\Booking\Enums\BookingStatus;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Resources\BookingResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BookingController extends Controller
{
    public function __construct(
        private GetAllBookingsHandler $getAllBookingsHandler,
        private GetBookingByIdHandler $getBookingByIdHandler,
        private GetBookingStatisticsHandler $getBookingStatisticsHandler,
        private GetBookingDisputesHandler $getBookingDisputesHandler,
        private UpdateBookingStatusHandler $updateBookingStatusHandler,
        private CancelBookingHandler $cancelBookingHandler,
        private BookingRepositoryInterface $bookingRepository
    ) {}

    /**
     * Lister toutes les réservations
     */
    #[OA\Get(
        path: '/api/v1/admin/bookings',
        summary: 'Lister toutes les réservations',
        description: 'Récupère la liste paginée de toutes les réservations avec filtres optionnels',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'Filtrer par statut',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['pending', 'confirmed', 'cancelled', 'completed', 'disputed', 'refunded'])
            ),
            new OA\Parameter(
                name: 'payment_status',
                in: 'query',
                description: 'Filtrer par statut de paiement',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['pending', 'paid', 'failed', 'refunded'])
            ),
            new OA\Parameter(
                name: 'experience_id',
                in: 'query',
                description: 'Filtrer par expérience',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'traveler_id',
                in: 'query',
                description: 'Filtrer par voyageur',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'provider_id',
                in: 'query',
                description: 'Filtrer par prestataire',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'date_from',
                in: 'query',
                description: 'Date de début (YYYY-MM-DD)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'date_to',
                in: 'query',
                description: 'Date de fin (YYYY-MM-DD)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Numéro de page',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: 'Nombre d\'éléments par page',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des réservations',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'uuid', type: 'string'),
                                    new OA\Property(property: 'status', type: 'string'),
                                    new OA\Property(property: 'total_amount', type: 'number', format: 'float'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer'),
                                new OA\Property(property: 'last_page', type: 'integer'),
                                new OA\Property(property: 'per_page', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = new GetAllBookingsQuery(
            status: $request->input('status'),
            paymentStatus: $request->input('payment_status'),
            experienceId: $request->input('experience_id') ? (int) $request->input('experience_id') : null,
            travelerId: $request->input('traveler_id') ? (int) $request->input('traveler_id') : null,
            providerId: $request->input('provider_id') ? (int) $request->input('provider_id') : null,
            dateFrom: $request->input('date_from'),
            dateTo: $request->input('date_to'),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $bookings = $this->getAllBookingsHandler->handle($query);

        return ApiResponse::paginated(
            $bookings,
            'Liste des réservations récupérée avec succès',
            fn($booking) => new BookingResource($booking)
        );
    }

    /**
     * Consulter une réservation en détail
     */
    #[OA\Get(
        path: '/api/v1/admin/bookings/{id}',
        summary: 'Consulter une réservation en détail',
        description: 'Récupère les informations complètes d\'une réservation par son ID',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la réservation',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détails de la réservation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'uuid', type: 'string'),
                                new OA\Property(property: 'status', type: 'string'),
                                new OA\Property(property: 'total_amount', type: 'number', format: 'float'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Réservation non trouvée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $query = new GetBookingByIdQuery(bookingId: (int) $id);
        $booking = $this->getBookingByIdHandler->handle($query);

        if (!$booking) {
            return ApiResponse::error('Réservation non trouvée', 404);
        }

        return ApiResponse::success(new BookingResource($booking), 'Réservation récupérée avec succès');
    }

    /**
     * Modifier le statut d'une réservation
     */
    #[OA\Put(
        path: '/api/v1/admin/bookings/{id}/status',
        summary: 'Modifier le statut d\'une réservation',
        description: 'Modifie le statut d\'une réservation (confirmed, completed, etc.)',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la réservation',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['pending', 'confirmed', 'cancelled', 'completed', 'disputed', 'refunded'],
                        example: 'confirmed'
                    ),
                    new OA\Property(property: 'reason', type: 'string', nullable: true, example: 'Réservation confirmée par l\'admin'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statut modifié avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Statut modifié avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'status', type: 'string'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Réservation non trouvée'),
            new OA\Response(response: 400, description: 'Statut invalide'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        try {
            $status = BookingStatus::from($request->input('status'));

            $command = new UpdateBookingStatusCommand(
                bookingId: (int) $id,
                status: $status,
                reason: $request->input('reason')
            );
            $booking = $this->updateBookingStatusHandler->handle($command);

            return ApiResponse::success(new BookingResource($booking), 'Statut modifié avec succès');
        } catch (\ValueError $e) {
            return ApiResponse::error('Statut invalide', 400);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Annuler une réservation
     */
    #[OA\Put(
        path: '/api/v1/admin/bookings/{id}/cancel',
        summary: 'Annuler une réservation',
        description: 'Annule une réservation avec une raison optionnelle',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de la réservation',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'reason', type: 'string', nullable: true, example: 'Annulation demandée par le client'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Réservation annulée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Réservation annulée avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'status', type: 'string', example: 'cancelled'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Réservation non trouvée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function cancel(Request $request, string $id): JsonResponse
    {
        try {
            $command = new CancelBookingCommand(
                bookingId: (int) $id,
                reason: $request->input('reason'),
                cancelledBy: auth()->id()
            );
            $booking = $this->cancelBookingHandler->handle($command);

            return ApiResponse::success(new BookingResource($booking), 'Réservation annulée avec succès');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Statistiques des réservations
     */
    #[OA\Get(
        path: '/api/v1/admin/bookings/statistics',
        summary: 'Statistiques des réservations',
        description: 'Récupère les statistiques globales des réservations',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistiques des réservations',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total', type: 'integer', example: 150),
                                new OA\Property(
                                    property: 'by_status',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'pending', type: 'integer', example: 20),
                                        new OA\Property(property: 'confirmed', type: 'integer', example: 100),
                                        new OA\Property(property: 'cancelled', type: 'integer', example: 15),
                                        new OA\Property(property: 'completed', type: 'integer', example: 10),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'by_payment_status',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'paid', type: 'integer', example: 120),
                                        new OA\Property(property: 'pending', type: 'integer', example: 20),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'bookings',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'today', type: 'integer', example: 5),
                                        new OA\Property(property: 'this_week', type: 'integer', example: 25),
                                        new OA\Property(property: 'this_month', type: 'integer', example: 80),
                                    ]
                                ),
                                new OA\Property(property: 'total_revenue', type: 'number', format: 'float', example: 1500000.00),
                                new OA\Property(property: 'disputes_count', type: 'integer', example: 5),
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
        $query = new GetBookingStatisticsQuery();
        $statistics = $this->getBookingStatisticsHandler->handle($query);

        return ApiResponse::success($statistics, 'Statistiques récupérées avec succès');
    }

    /**
     * Litiges de réservation
     */
    #[OA\Get(
        path: '/api/v1/admin/bookings/disputes',
        summary: 'Litiges de réservation',
        description: 'Récupère la liste des litiges de réservation',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Numéro de page',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: 'Nombre d\'éléments par page',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des litiges',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'reason', type: 'string'),
                                    new OA\Property(property: 'description', type: 'string'),
                                    new OA\Property(property: 'status', type: 'string'),
                                    new OA\Property(
                                        property: 'booking',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer'),
                                            new OA\Property(property: 'uuid', type: 'string'),
                                        ]
                                    ),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer'),
                                new OA\Property(property: 'last_page', type: 'integer'),
                                new OA\Property(property: 'per_page', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function disputes(Request $request): JsonResponse
    {
        $query = new GetBookingDisputesQuery(
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $disputes = $this->getBookingDisputesHandler->handle($query);

        return ApiResponse::paginated($disputes, 'Liste des litiges récupérée avec succès');
    }
}


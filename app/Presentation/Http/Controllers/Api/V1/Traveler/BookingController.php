<?php

namespace App\Presentation\Http\Controllers\Api\V1\Traveler;

use App\Application\Traveler\Booking\Commands\CancelBookingCommand;
use App\Application\Traveler\Booking\Commands\CreateBookingCommand;
use App\Application\Traveler\Booking\Handlers\CancelBookingHandler;
use App\Application\Traveler\Booking\Handlers\CreateBookingHandler;
use App\Application\Traveler\Booking\Handlers\GetBookingByIdHandler;
use App\Application\Traveler\Booking\Handlers\GetBookingHistoryHandler;
use App\Application\Traveler\Booking\Handlers\GetBookingsHandler;
use App\Application\Traveler\Booking\Handlers\GetConfirmedBookingsHandler;
use App\Application\Traveler\Booking\Handlers\GetPendingBookingsHandler;
use App\Application\Traveler\Booking\Handlers\GetUpcomingBookingsHandler;
use App\Application\Traveler\Booking\Queries\GetBookingByIdQuery;
use App\Application\Traveler\Booking\Queries\GetBookingHistoryQuery;
use App\Application\Traveler\Booking\Queries\GetBookingsQuery;
use App\Application\Traveler\Booking\Queries\GetConfirmedBookingsQuery;
use App\Application\Traveler\Booking\Queries\GetPendingBookingsQuery;
use App\Application\Traveler\Booking\Queries\GetUpcomingBookingsQuery;
use App\Presentation\Http\Controllers\Api\V1\Auth\BaseController;
use App\Presentation\Http\Requests\Traveler\CancelBookingRequest;
use App\Presentation\Http\Requests\Traveler\CreateBookingRequest;
use App\Presentation\Http\Resources\BookingResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Voyageur - Réservations",
    description: "Endpoints pour la gestion des réservations par les voyageurs"
)]
class BookingController extends BaseController
{
    public function __construct(
        private GetBookingsHandler $getBookingsHandler,
        private GetBookingByIdHandler $getBookingByIdHandler,
        private CreateBookingHandler $createBookingHandler,
        private CancelBookingHandler $cancelBookingHandler,
        private GetUpcomingBookingsHandler $getUpcomingBookingsHandler,
        private GetPendingBookingsHandler $getPendingBookingsHandler,
        private GetConfirmedBookingsHandler $getConfirmedBookingsHandler,
        private GetBookingHistoryHandler $getBookingHistoryHandler
    ) {}

    /**
     * Mes réservations
     */
    #[OA\Get(
        path: '/api/v1/traveler/bookings',
        summary: 'Lister mes réservations',
        description: 'Récupère la liste paginée de toutes les réservations du voyageur authentifié avec filtres optionnels.',
        tags: ['Voyageur - Réservations'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', description: 'Filtrer par statut', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'confirmed', 'cancelled', 'completed', 'disputed', 'refunded'])),
            new OA\Parameter(name: 'payment_status', in: 'query', description: 'Filtrer par statut de paiement', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'paid', 'failed', 'refunded'])),
            new OA\Parameter(name: 'date_from', in: 'query', description: 'Date de début (YYYY-MM-DD)', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', description: 'Date de fin (YYYY-MM-DD)', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des réservations récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Liste des réservations récupérée avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Booking')),
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
        $query = new GetBookingsQuery(
            travelerId: auth()->id(),
            status: $request->input('status'),
            paymentStatus: $request->input('payment_status'),
            dateFrom: $request->input('date_from'),
            dateTo: $request->input('date_to'),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15)
        );

        $bookings = $this->getBookingsHandler->handle($query);

        return ApiResponse::paginated(
            $bookings,
            'Liste des réservations récupérée avec succès',
            fn($booking) => new BookingResource($booking)
        );
    }

    /**
     * Effectuer une réservation
     */
    #[OA\Post(
        path: '/api/v1/traveler/bookings',
        summary: 'Effectuer une réservation',
        description: 'Crée une nouvelle réservation pour une expérience. Vérifie la disponibilité et calcule automatiquement le montant total.',
        tags: ['Voyageur - Réservations'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['experience_id', 'booking_date', 'participants_count'],
                properties: [
                    new OA\Property(property: 'experience_id', type: 'integer', example: 1),
                    new OA\Property(property: 'booking_date', type: 'string', format: 'date', example: '2026-03-15'),
                    new OA\Property(property: 'booking_time', type: 'string', format: 'time', nullable: true, example: '14:00'),
                    new OA\Property(property: 'participants_count', type: 'integer', minimum: 1, example: 2),
                    new OA\Property(property: 'special_requests', type: 'string', nullable: true, example: 'Besoin d\'un guide francophone'),
                    new OA\Property(property: 'payment_method', type: 'string', nullable: true, example: 'mobile_money'),
                    new OA\Property(property: 'metadata', type: 'object', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Réservation créée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Réservation créée avec succès'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Booking'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreurs de validation'),
        ]
    )]
    public function store(CreateBookingRequest $request): JsonResponse
    {
        try {
            $command = new CreateBookingCommand(
                travelerId: auth()->id(),
                experienceId: $request->input('experience_id'),
                bookingDate: $request->input('booking_date'),
                bookingTime: $request->input('booking_time'),
                participantsCount: $request->input('participants_count'),
                specialRequests: $request->input('special_requests'),
                paymentMethod: $request->input('payment_method'),
                metadata: $request->input('metadata')
            );

            $booking = $this->createBookingHandler->handle($command);

            return ApiResponse::success(
                new BookingResource($booking->load(['experience', 'provider'])),
                'Réservation créée avec succès',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Détails d'une réservation
     */
    #[OA\Get(
        path: '/api/v1/traveler/bookings/{id}',
        summary: 'Consulter les détails d\'une réservation',
        description: 'Récupère les informations détaillées d\'une réservation spécifique appartenant au voyageur authentifié.',
        tags: ['Voyageur - Réservations'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'ID de la réservation', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détails de la réservation récupérés avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Détails de la réservation récupérés avec succès'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Booking'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Réservation non trouvée'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $query = new GetBookingByIdQuery(
            bookingId: $id,
            travelerId: auth()->id()
        );

        $booking = $this->getBookingByIdHandler->handle($query);

        if (!$booking) {
            return ApiResponse::error('Réservation non trouvée', 404);
        }

        return ApiResponse::success(
            new BookingResource($booking->load(['experience', 'provider'])),
            'Détails de la réservation récupérés avec succès'
        );
    }

    /**
     * Annuler une réservation
     */
    #[OA\Put(
        path: '/api/v1/traveler/bookings/{id}/cancel',
        summary: 'Annuler une réservation',
        description: 'Annule une réservation appartenant au voyageur authentifié. Seules les réservations en attente ou confirmées peuvent être annulées.',
        tags: ['Voyageur - Réservations'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'ID de la réservation à annuler', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'reason', type: 'string', nullable: true, example: 'Changement de plan'),
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
                        new OA\Property(property: 'data', ref: '#/components/schemas/Booking'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Réservation non trouvée'),
            new OA\Response(response: 400, description: 'Impossible d\'annuler cette réservation'),
        ]
    )]
    public function cancel(CancelBookingRequest $request, int $id): JsonResponse
    {
        try {
            $command = new CancelBookingCommand(
                bookingId: $id,
                travelerId: auth()->id(),
                reason: $request->input('reason')
            );

            $booking = $this->cancelBookingHandler->handle($command);

            return ApiResponse::success(
                new BookingResource($booking->load(['experience', 'provider'])),
                'Réservation annulée avec succès'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Réservations à venir
     */
    #[OA\Get(
        path: '/api/v1/traveler/bookings/upcoming',
        summary: 'Réservations à venir',
        description: 'Récupère la liste des réservations à venir (date >= aujourd\'hui) avec statut pending ou confirmed.',
        tags: ['Voyageur - Réservations'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Réservations à venir récupérées avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Réservations à venir récupérées avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Booking')),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function upcoming(Request $request): JsonResponse
    {
        $query = new GetUpcomingBookingsQuery(
            travelerId: auth()->id(),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15)
        );

        $bookings = $this->getUpcomingBookingsHandler->handle($query);

        return ApiResponse::paginated(
            $bookings,
            'Réservations à venir récupérées avec succès',
            fn($booking) => new BookingResource($booking)
        );
    }

    /**
     * Réservations en attente
     */
    #[OA\Get(
        path: '/api/v1/traveler/bookings/pending',
        summary: 'Réservations en attente',
        description: 'Récupère la liste des réservations avec le statut "pending".',
        tags: ['Voyageur - Réservations'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Réservations en attente récupérées avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Réservations en attente récupérées avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Booking')),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function pending(Request $request): JsonResponse
    {
        $query = new GetPendingBookingsQuery(
            travelerId: auth()->id(),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15)
        );

        $bookings = $this->getPendingBookingsHandler->handle($query);

        return ApiResponse::paginated(
            $bookings,
            'Réservations en attente récupérées avec succès',
            fn($booking) => new BookingResource($booking)
        );
    }

    /**
     * Réservations confirmées
     */
    #[OA\Get(
        path: '/api/v1/traveler/bookings/confirmed',
        summary: 'Réservations confirmées',
        description: 'Récupère la liste des réservations avec le statut "confirmed".',
        tags: ['Voyageur - Réservations'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Réservations confirmées récupérées avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Réservations confirmées récupérées avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Booking')),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function confirmed(Request $request): JsonResponse
    {
        $query = new GetConfirmedBookingsQuery(
            travelerId: auth()->id(),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15)
        );

        $bookings = $this->getConfirmedBookingsHandler->handle($query);

        return ApiResponse::paginated(
            $bookings,
            'Réservations confirmées récupérées avec succès',
            fn($booking) => new BookingResource($booking)
        );
    }

    /**
     * Historique des réservations
     */
    #[OA\Get(
        path: '/api/v1/traveler/bookings/history',
        summary: 'Historique des réservations',
        description: 'Récupère l\'historique des réservations terminées, annulées ou remboursées.',
        tags: ['Voyageur - Réservations'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Historique des réservations récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Historique des réservations récupéré avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Booking')),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function history(Request $request): JsonResponse
    {
        $query = new GetBookingHistoryQuery(
            travelerId: auth()->id(),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15)
        );

        $bookings = $this->getBookingHistoryHandler->handle($query);

        return ApiResponse::paginated(
            $bookings,
            'Historique des réservations récupéré avec succès',
            fn($booking) => new BookingResource($booking)
        );
    }

    /**
     * Télécharger le bon de réservation
     */
    #[OA\Get(
        path: '/api/v1/traveler/bookings/{id}/voucher',
        summary: 'Télécharger le bon de réservation',
        description: 'Génère et retourne les informations du bon de réservation (voucher) pour une réservation confirmée. Pour l\'instant, retourne les données JSON. Le PDF sera ajouté ultérieurement.',
        tags: ['Voyageur - Réservations'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'ID de la réservation', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Bon de réservation récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Bon de réservation récupéré avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'booking', ref: '#/components/schemas/Booking'),
                                new OA\Property(property: 'voucher_number', type: 'string', example: 'VCH-2026-001234'),
                                new OA\Property(property: 'qr_code', type: 'string', nullable: true, description: 'URL ou données du QR code'),
                                new OA\Property(property: 'download_url', type: 'string', nullable: true, description: 'URL de téléchargement du PDF (à venir)'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Réservation non trouvée'),
            new OA\Response(response: 400, description: 'Le bon de réservation n\'est disponible que pour les réservations confirmées'),
        ]
    )]
    public function voucher(int $id): JsonResponse
    {
        $query = new GetBookingByIdQuery(
            bookingId: $id,
            travelerId: auth()->id()
        );

        $booking = $this->getBookingByIdHandler->handle($query);

        if (!$booking) {
            return ApiResponse::error('Réservation non trouvée', 404);
        }

        if ($booking->status->value !== 'confirmed') {
            return ApiResponse::error('Le bon de réservation n\'est disponible que pour les réservations confirmées', 400);
        }

        // Générer le numéro de voucher
        $voucherNumber = 'VCH-' . date('Y') . '-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT);

        return ApiResponse::success([
            'booking' => new BookingResource($booking->load(['experience', 'provider'])),
            'voucher_number' => $voucherNumber,
            'voucher_date' => now()->toIso8601String(),
            'qr_code' => null, // À implémenter avec une bibliothèque QR code
            'download_url' => null, // À implémenter avec génération PDF
            'note' => 'Le téléchargement PDF sera disponible prochainement',
        ], 'Bon de réservation récupéré avec succès');
    }
}



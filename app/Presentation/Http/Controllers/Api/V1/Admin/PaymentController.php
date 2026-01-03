<?php

namespace App\Presentation\Http\Controllers\Api\V1\Admin;

use App\Application\Payment\Commands\RefundPaymentCommand;
use App\Application\Payment\Commands\TransferPaymentCommand;
use App\Application\Payment\Handlers\GetAllPaymentsHandler;
use App\Application\Payment\Handlers\GetCommissionsHandler;
use App\Application\Payment\Handlers\GetPaymentByIdHandler;
use App\Application\Payment\Handlers\GetPaymentDisputesHandler;
use App\Application\Payment\Handlers\GetPaymentStatisticsHandler;
use App\Application\Payment\Handlers\RefundPaymentHandler;
use App\Application\Payment\Handlers\TransferPaymentHandler;
use App\Application\Payment\Queries\GetAllPaymentsQuery;
use App\Application\Payment\Queries\GetCommissionsQuery;
use App\Application\Payment\Queries\GetPaymentByIdQuery;
use App\Application\Payment\Queries\GetPaymentDisputesQuery;
use App\Application\Payment\Queries\GetPaymentStatisticsQuery;
use App\Infrastructure\Repositories\Contracts\PaymentRepositoryInterface;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Resources\PaymentResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PaymentController extends Controller
{
    public function __construct(
        private GetAllPaymentsHandler $getAllPaymentsHandler,
        private GetPaymentByIdHandler $getPaymentByIdHandler,
        private GetPaymentStatisticsHandler $getPaymentStatisticsHandler,
        private GetPaymentDisputesHandler $getPaymentDisputesHandler,
        private GetCommissionsHandler $getCommissionsHandler,
        private RefundPaymentHandler $refundPaymentHandler,
        private TransferPaymentHandler $transferPaymentHandler,
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    /**
     * Lister tous les paiements
     */
    #[OA\Get(
        path: '/api/v1/admin/payments',
        summary: 'Lister tous les paiements',
        description: 'Récupère la liste paginée de tous les paiements avec filtres optionnels',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'processing', 'completed', 'failed', 'refunded', 'partially_refunded', 'cancelled'])),
            new OA\Parameter(name: 'type', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['booking', 'refund', 'commission', 'transfer'])),
            new OA\Parameter(name: 'booking_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'provider_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des paiements'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = new GetAllPaymentsQuery(
            status: $request->input('status'),
            type: $request->input('type'),
            bookingId: $request->input('booking_id') ? (int) $request->input('booking_id') : null,
            providerId: $request->input('provider_id') ? (int) $request->input('provider_id') : null,
            dateFrom: $request->input('date_from'),
            dateTo: $request->input('date_to'),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $payments = $this->getAllPaymentsHandler->handle($query);

        return ApiResponse::paginated(
            $payments,
            'Liste des paiements récupérée avec succès',
            fn($payment) => new PaymentResource($payment)
        );
    }

    /**
     * Détails d'un paiement
     */
    #[OA\Get(
        path: '/api/v1/admin/payments/{id}',
        summary: 'Détails d\'un paiement',
        description: 'Récupère les informations complètes d\'un paiement par son ID',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails du paiement'),
            new OA\Response(response: 404, description: 'Paiement non trouvé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $query = new GetPaymentByIdQuery(paymentId: (int) $id);
        $payment = $this->getPaymentByIdHandler->handle($query);

        if (!$payment) {
            return ApiResponse::error('Paiement non trouvé', 404);
        }

        return ApiResponse::success(new PaymentResource($payment), 'Paiement récupéré avec succès');
    }

    /**
     * Traiter un remboursement
     */
    #[OA\Post(
        path: '/api/v1/admin/payments/refund',
        summary: 'Traiter un remboursement',
        description: 'Traite un remboursement partiel ou total pour un paiement',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['payment_id', 'amount'],
                properties: [
                    new OA\Property(property: 'payment_id', type: 'integer', example: 1),
                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 15000.00),
                    new OA\Property(property: 'reason', type: 'string', nullable: true, example: 'Remboursement demandé par le client'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Remboursement traité avec succès'),
            new OA\Response(response: 404, description: 'Paiement non trouvé'),
            new OA\Response(response: 400, description: 'Montant invalide'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function refund(Request $request): JsonResponse
    {
        try {
            $command = new RefundPaymentCommand(
                paymentId: (int) $request->input('payment_id'),
                amount: (float) $request->input('amount'),
                reason: $request->input('reason')
            );
            $payment = $this->refundPaymentHandler->handle($command);

            return ApiResponse::success(new PaymentResource($payment), 'Remboursement traité avec succès');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Statistiques financières
     */
    #[OA\Get(
        path: '/api/v1/admin/payments/statistics',
        summary: 'Statistiques financières',
        description: 'Récupère les statistiques financières des paiements',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistiques financières',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total', type: 'integer', example: 200),
                                new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 5000000.00),
                                new OA\Property(property: 'total_commission', type: 'number', format: 'float', example: 500000.00),
                                new OA\Property(property: 'total_refunded', type: 'number', format: 'float', example: 50000.00),
                                new OA\Property(property: 'disputes_count', type: 'integer', example: 5),
                                new OA\Property(property: 'pending_transfers', type: 'integer', example: 10),
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
        $query = new GetPaymentStatisticsQuery();
        $statistics = $this->getPaymentStatisticsHandler->handle($query);

        return ApiResponse::success($statistics, 'Statistiques récupérées avec succès');
    }

    /**
     * Litiges de paiement
     */
    #[OA\Get(
        path: '/api/v1/admin/payments/disputes',
        summary: 'Litiges de paiement',
        description: 'Récupère la liste des litiges de paiement',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des litiges'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function disputes(Request $request): JsonResponse
    {
        $query = new GetPaymentDisputesQuery(
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $disputes = $this->getPaymentDisputesHandler->handle($query);

        return ApiResponse::paginated($disputes, 'Liste des litiges récupérée avec succès');
    }

    /**
     * Suivi des commissions plateforme
     */
    #[OA\Get(
        path: '/api/v1/admin/payments/commissions',
        summary: 'Suivi des commissions plateforme',
        description: 'Récupère la liste des commissions de la plateforme',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'provider_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des commissions'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function commissions(Request $request): JsonResponse
    {
        $query = new GetCommissionsQuery(
            providerId: $request->input('provider_id') ? (int) $request->input('provider_id') : null,
            dateFrom: $request->input('date_from'),
            dateTo: $request->input('date_to'),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $commissions = $this->getCommissionsHandler->handle($query);

        return ApiResponse::paginated(
            $commissions,
            'Liste des commissions récupérée avec succès',
            fn($payment) => new PaymentResource($payment)
        );
    }

    /**
     * Transférer les fonds au prestataire
     */
    #[OA\Put(
        path: '/api/v1/admin/payments/{id}/transfer',
        summary: 'Transférer les fonds au prestataire',
        description: 'Transfère les fonds d\'un paiement au prestataire',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Fonds transférés avec succès'),
            new OA\Response(response: 404, description: 'Paiement non trouvé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function transfer(string $id): JsonResponse
    {
        try {
            $command = new TransferPaymentCommand(paymentId: (int) $id);
            $payment = $this->transferPaymentHandler->handle($command);

            return ApiResponse::success(new PaymentResource($payment), 'Fonds transférés avec succès');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }
}


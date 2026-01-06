<?php

namespace App\Presentation\Http\Controllers\Api\V1\Traveler;

use App\Application\Traveler\Notification\Commands\MarkAsReadCommand;
use App\Application\Traveler\Notification\Commands\UpdateNotificationSettingsCommand;
use App\Application\Traveler\Notification\Handlers\GetNotificationsHandler;
use App\Application\Traveler\Notification\Handlers\GetUnreadNotificationsHandler;
use App\Application\Traveler\Notification\Handlers\MarkAsReadHandler;
use App\Application\Traveler\Notification\Handlers\UpdateNotificationSettingsHandler;
use App\Application\Traveler\Notification\Queries\GetNotificationsQuery;
use App\Application\Traveler\Notification\Queries\GetUnreadNotificationsQuery;
use App\Infrastructure\Repositories\Contracts\NotificationRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\NotificationSettingRepositoryInterface;
use App\Presentation\Http\Controllers\Api\V1\Auth\BaseController;
use App\Presentation\Http\Requests\Traveler\Notification\UpdateNotificationSettingsRequest;
use App\Presentation\Http\Resources\NotificationResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Voyageur - Notifications",
    description: "Endpoints pour la gestion des notifications par les voyageurs"
)]
class NotificationController extends BaseController
{
    public function __construct(
        private GetNotificationsHandler $getNotificationsHandler,
        private GetUnreadNotificationsHandler $getUnreadNotificationsHandler,
        private MarkAsReadHandler $markAsReadHandler,
        private UpdateNotificationSettingsHandler $updateNotificationSettingsHandler,
        private NotificationRepositoryInterface $notificationRepository,
        private NotificationSettingRepositoryInterface $notificationSettingRepository
    ) {}

    /**
     * Mes notifications
     */
    #[OA\Get(
        path: '/api/v1/traveler/notifications',
        summary: 'Mes notifications',
        description: 'Récupère la liste paginée de toutes les notifications du voyageur authentifié.',
        tags: ['Voyageur - Notifications'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'type', in: 'query', description: 'Filtrer par type', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'is_read', in: 'query', description: 'Filtrer par statut de lecture', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des notifications récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Liste des notifications récupérée avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Notification')),
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
            'type' => $request->input('type'),
            'is_read' => $request->input('is_read'),
        ], fn($value) => $value !== null);

        $query = new GetNotificationsQuery(
            userId: auth()->id(),
            filters: $filters,
            perPage: (int) $request->input('per_page', 15)
        );

        $notifications = $this->getNotificationsHandler->handle($query);

        return ApiResponse::paginated(
            $notifications,
            'Liste des notifications récupérée avec succès',
            fn($notification) => new NotificationResource($notification)
        );
    }

    /**
     * Marquer comme lue
     */
    #[OA\Put(
        path: '/api/v1/traveler/notifications/{id}/read',
        summary: 'Marquer une notification comme lue',
        description: 'Marque une notification spécifique comme lue.',
        tags: ['Voyageur - Notifications'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de la notification', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification marquée comme lue',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Notification marquée comme lue'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Notification'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Notification non trouvée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function markAsRead(int $id): JsonResponse
    {
        $command = new MarkAsReadCommand(
            userId: auth()->id(),
            notificationId: $id
        );

        try {
            $notification = $this->markAsReadHandler->handle($command);
            return ApiResponse::success(
                new NotificationResource($notification),
                'Notification marquée comme lue'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Notifications non lues
     */
    #[OA\Get(
        path: '/api/v1/traveler/notifications/unread',
        summary: 'Notifications non lues',
        description: 'Récupère la liste des notifications non lues du voyageur authentifié.',
        tags: ['Voyageur - Notifications'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des notifications non lues récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Liste des notifications non lues récupérée avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Notification')),
                        new OA\Property(property: 'unread_count', type: 'integer', example: 5),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function unread(): JsonResponse
    {
        $query = new GetUnreadNotificationsQuery(userId: auth()->id());
        $notifications = $this->getUnreadNotificationsHandler->handle($query);
        $count = $this->notificationRepository->getUnreadCount(auth()->id());

        return ApiResponse::success([
            'notifications' => NotificationResource::collection($notifications),
            'unread_count' => $count,
        ], 'Liste des notifications non lues récupérée avec succès');
    }

    /**
     * Paramètres de notifications
     */
    #[OA\Put(
        path: '/api/v1/traveler/notifications/settings',
        summary: 'Paramètres de notifications',
        description: 'Met à jour les paramètres de notifications du voyageur authentifié.',
        tags: ['Voyageur - Notifications'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email_enabled', type: 'boolean', example: true),
                    new OA\Property(property: 'sms_enabled', type: 'boolean', example: false),
                    new OA\Property(property: 'push_enabled', type: 'boolean', example: true),
                    new OA\Property(property: 'preferences', type: 'object', nullable: true, description: 'Préférences par type de notification'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paramètres de notifications mis à jour avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Paramètres de notifications mis à jour avec succès'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function updateSettings(UpdateNotificationSettingsRequest $request): JsonResponse
    {
        $command = new UpdateNotificationSettingsCommand(
            userId: auth()->id(),
            emailEnabled: $request->input('email_enabled'),
            smsEnabled: $request->input('sms_enabled'),
            pushEnabled: $request->input('push_enabled'),
            preferences: $request->input('preferences')
        );

        $settings = $this->updateNotificationSettingsHandler->handle($command);

        return ApiResponse::success($settings, 'Paramètres de notifications mis à jour avec succès');
    }
}



<?php

namespace App\Presentation\Http\Controllers\Api\V1\Traveler;

use App\Application\Traveler\Message\Commands\ContactProviderCommand;
use App\Application\Traveler\Message\Commands\ReplyToMessageCommand;
use App\Application\Traveler\Message\Handlers\ContactProviderHandler;
use App\Application\Traveler\Message\Handlers\GetConversationHandler;
use App\Application\Traveler\Message\Handlers\GetConversationsHandler;
use App\Application\Traveler\Message\Handlers\GetUnreadMessagesHandler;
use App\Application\Traveler\Message\Handlers\ReplyToMessageHandler;
use App\Application\Traveler\Message\Queries\GetConversationQuery;
use App\Application\Traveler\Message\Queries\GetConversationsQuery;
use App\Application\Traveler\Message\Queries\GetUnreadMessagesQuery;
use App\Infrastructure\Repositories\Contracts\MessageRepositoryInterface;
use App\Presentation\Http\Controllers\Api\V1\Auth\BaseController;
use App\Presentation\Http\Requests\Traveler\Message\ContactProviderRequest;
use App\Presentation\Http\Requests\Traveler\Message\ReplyToMessageRequest;
use App\Presentation\Http\Resources\ConversationResource;
use App\Presentation\Http\Resources\MessageResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Voyageur - Communication",
    description: "Endpoints pour la communication entre voyageurs et prestataires"
)]
class MessageController extends BaseController
{
    public function __construct(
        private GetConversationsHandler $getConversationsHandler,
        private GetConversationHandler $getConversationHandler,
        private GetUnreadMessagesHandler $getUnreadMessagesHandler,
        private ContactProviderHandler $contactProviderHandler,
        private ReplyToMessageHandler $replyToMessageHandler,
        private MessageRepositoryInterface $messageRepository
    ) {}

    /**
     * Mes conversations
     */
    #[OA\Get(
        path: '/api/v1/traveler/messages',
        summary: 'Mes conversations',
        description: 'Récupère la liste paginée de toutes les conversations du voyageur authentifié.',
        tags: ['Voyageur - Communication'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', description: 'Filtrer par statut', required: false, schema: new OA\Schema(type: 'string', enum: ['active', 'archived', 'blocked'])),
            new OA\Parameter(name: 'provider_id', in: 'query', description: 'Filtrer par prestataire', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des conversations récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Liste des conversations récupérée avec succès'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Conversation')),
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
            'provider_id' => $request->input('provider_id'),
        ], fn($value) => $value !== null);

        $query = new GetConversationsQuery(
            travelerId: auth()->id(),
            filters: $filters,
            perPage: (int) $request->input('per_page', 15)
        );

        $conversations = $this->getConversationsHandler->handle($query);

        return ApiResponse::paginated(
            $conversations,
            'Liste des conversations récupérée avec succès',
            fn($conversation) => new ConversationResource($conversation)
        );
    }

    /**
     * Contacter un prestataire
     */
    #[OA\Post(
        path: '/api/v1/traveler/messages/providers/{providerId}',
        summary: 'Contacter un prestataire',
        description: 'Crée une nouvelle conversation ou envoie un message à un prestataire.',
        tags: ['Voyageur - Communication'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'providerId', in: 'path', required: true, description: 'ID du prestataire', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['message'],
                properties: [
                    new OA\Property(property: 'experience_id', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'booking_id', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'subject', type: 'string', nullable: true, example: 'Question sur l\'expérience'),
                    new OA\Property(property: 'message', type: 'string', example: 'Bonjour, j\'aimerais avoir plus d\'informations...'),
                    new OA\Property(property: 'attachments', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Message envoyé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Message envoyé avec succès'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Conversation'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function contactProvider(ContactProviderRequest $request, int $providerId): JsonResponse
    {
        $command = new ContactProviderCommand(
            travelerId: auth()->id(),
            providerId: $providerId,
            experienceId: $request->input('experience_id'),
            bookingId: $request->input('booking_id'),
            subject: $request->input('subject'),
            message: $request->input('message'),
            attachments: $request->input('attachments')
        );

        try {
            $conversation = $this->contactProviderHandler->handle($command);
            return ApiResponse::success(
                new ConversationResource($conversation),
                'Message envoyé avec succès',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Consulter une conversation
     */
    #[OA\Get(
        path: '/api/v1/traveler/messages/{conversationId}',
        summary: 'Consulter une conversation',
        description: 'Récupère les détails d\'une conversation spécifique avec tous ses messages.',
        tags: ['Voyageur - Communication'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'conversationId', in: 'path', required: true, description: 'ID ou UUID de la conversation', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page pour les messages', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Messages par page', required: false, schema: new OA\Schema(type: 'integer', default: 50)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Conversation récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Conversation récupérée avec succès'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Conversation'),
                        new OA\Property(property: 'messages', type: 'array', items: new OA\Items(ref: '#/components/schemas/Message')),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Conversation non trouvée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function show(Request $request, string $conversationId): JsonResponse
    {
        $query = new GetConversationQuery(
            travelerId: auth()->id(),
            conversationId: $conversationId
        );

        $conversation = $this->getConversationHandler->handle($query);

        if (!$conversation) {
            return ApiResponse::error('Conversation non trouvée', 404);
        }

        // Marquer les messages comme lus
        $this->messageRepository->markConversationAsRead($conversation->id, auth()->id());

        // Récupérer les messages paginés
        $messages = $this->messageRepository->findByConversationId(
            $conversation->id,
            (int) $request->input('per_page', 50)
        );

        return ApiResponse::success([
            'conversation' => new ConversationResource($conversation->fresh()),
            'messages' => ApiResponse::paginated(
                $messages,
                null,
                fn($message) => new MessageResource($message)
            ),
        ], 'Conversation récupérée avec succès');
    }

    /**
     * Répondre à un message
     */
    #[OA\Post(
        path: '/api/v1/traveler/messages/{conversationId}/reply',
        summary: 'Répondre à un message',
        description: 'Envoie une réponse dans une conversation existante.',
        tags: ['Voyageur - Communication'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'conversationId', in: 'path', required: true, description: 'ID de la conversation', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['content'],
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Merci pour votre réponse...'),
                    new OA\Property(property: 'attachments', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Message envoyé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Message envoyé avec succès'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Conversation'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Conversation non trouvée'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function reply(ReplyToMessageRequest $request, int $conversationId): JsonResponse
    {
        $command = new ReplyToMessageCommand(
            travelerId: auth()->id(),
            conversationId: $conversationId,
            content: $request->input('content'),
            attachments: $request->input('attachments')
        );

        try {
            $conversation = $this->replyToMessageHandler->handle($command);
            return ApiResponse::success(
                new ConversationResource($conversation),
                'Message envoyé avec succès'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Messages non lus
     */
    #[OA\Get(
        path: '/api/v1/traveler/messages/unread',
        summary: 'Messages non lus',
        description: 'Récupère le nombre de conversations avec des messages non lus.',
        tags: ['Voyageur - Communication'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Nombre de messages non lus récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Nombre de messages non lus récupéré avec succès'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'unread_count', type: 'integer', example: 5),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function unread(): JsonResponse
    {
        $query = new GetUnreadMessagesQuery(travelerId: auth()->id());
        $count = $this->getUnreadMessagesHandler->handle($query);

        return ApiResponse::success(
            ['unread_count' => $count],
            'Nombre de messages non lus récupéré avec succès'
        );
    }
}



<?php

namespace App\Presentation\Http\Controllers\Api\V1\Admin;

use App\Application\Experience\Commands\DeleteExperienceCommand;
use App\Application\Experience\Commands\ModerateExperienceCommand;
use App\Application\Experience\Commands\UpdateExperienceCommand;
use App\Application\Experience\Handlers\DeleteExperienceHandler;
use App\Application\Experience\Handlers\GetAllExperiencesHandler;
use App\Application\Experience\Handlers\GetExperienceByIdHandler;
use App\Application\Experience\Handlers\GetExperienceReportsHandler;
use App\Application\Experience\Handlers\GetPendingExperiencesHandler;
use App\Application\Experience\Handlers\GetReportedExperiencesHandler;
use App\Application\Experience\Handlers\ModerateExperienceHandler;
use App\Application\Experience\Handlers\UpdateExperienceHandler;
use App\Application\Experience\Queries\GetAllExperiencesQuery;
use App\Application\Experience\Queries\GetExperienceByIdQuery;
use App\Application\Experience\Queries\GetExperienceReportsQuery;
use App\Application\Experience\Queries\GetPendingExperiencesQuery;
use App\Application\Experience\Queries\GetReportedExperiencesQuery;
use App\Domain\Experience\Enums\ExperienceStatus;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Resources\ExperienceResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ExperienceController extends Controller
{
    public function __construct(
        private GetAllExperiencesHandler $getAllExperiencesHandler,
        private GetExperienceByIdHandler $getExperienceByIdHandler,
        private GetPendingExperiencesHandler $getPendingExperiencesHandler,
        private GetReportedExperiencesHandler $getReportedExperiencesHandler,
        private GetExperienceReportsHandler $getExperienceReportsHandler,
        private UpdateExperienceHandler $updateExperienceHandler,
        private DeleteExperienceHandler $deleteExperienceHandler,
        private ModerateExperienceHandler $moderateExperienceHandler,
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    /**
     * Lister toutes les expériences
     */
    #[OA\Get(
        path: '/api/v1/admin/experiences',
        summary: 'Lister toutes les expériences',
        description: 'Récupère la liste paginée de toutes les expériences avec filtres optionnels',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'Filtrer par statut',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['draft', 'pending', 'approved', 'rejected', 'suspended', 'reported'])
            ),
            new OA\Parameter(
                name: 'type',
                in: 'query',
                description: 'Filtrer par type',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['activity', 'tour', 'workshop', 'event', 'accommodation', 'restaurant'])
            ),
            new OA\Parameter(
                name: 'provider_id',
                in: 'query',
                description: 'Filtrer par prestataire',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: 'Recherche par titre ou description',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'is_featured',
                in: 'query',
                description: 'Filtrer les expériences mises en avant',
                required: false,
                schema: new OA\Schema(type: 'boolean')
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
                description: 'Liste des expériences',
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
                                    new OA\Property(property: 'title', type: 'string'),
                                    new OA\Property(property: 'status', type: 'string'),
                                    new OA\Property(property: 'type', type: 'string'),
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
        $query = new GetAllExperiencesQuery(
            status: $request->input('status'),
            type: $request->input('type'),
            providerId: $request->input('provider_id') ? (int) $request->input('provider_id') : null,
            search: $request->input('search'),
            isFeatured: $request->input('is_featured') !== null ? (bool) $request->input('is_featured') : null,
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $experiences = $this->getAllExperiencesHandler->handle($query);

        return ApiResponse::paginated(
            $experiences,
            'Liste des expériences récupérée avec succès',
            fn($exp) => new ExperienceResource($exp)
        );
    }

    /**
     * Consulter une expérience en détail
     */
    #[OA\Get(
        path: '/api/v1/admin/experiences/{id}',
        summary: 'Consulter une expérience en détail',
        description: 'Récupère les informations complètes d\'une expérience par son ID',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de l\'expérience',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détails de l\'expérience',
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
                                new OA\Property(property: 'title', type: 'string'),
                                new OA\Property(property: 'status', type: 'string'),
                                new OA\Property(property: 'type', type: 'string'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Expérience non trouvée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $query = new GetExperienceByIdQuery(experienceId: (int) $id);
        $experience = $this->getExperienceByIdHandler->handle($query);

        if (!$experience) {
            return ApiResponse::error('Expérience non trouvée', 404);
        }

        return ApiResponse::success(new ExperienceResource($experience), 'Expérience récupérée avec succès');
    }

    /**
     * Modifier une expérience
     */
    #[OA\Put(
        path: '/api/v1/admin/experiences/{id}',
        summary: 'Modifier une expérience',
        description: 'Modifie une expérience (en cas d\'erreur)',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de l\'expérience',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', nullable: true),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'price', type: 'number', format: 'float', nullable: true),
                    new OA\Property(property: 'status', type: 'string', enum: ['draft', 'pending', 'approved', 'rejected', 'suspended', 'reported'], nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Expérience modifiée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Expérience modifiée avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'uuid', type: 'string'),
                                new OA\Property(property: 'title', type: 'string'),
                                new OA\Property(property: 'status', type: 'string'),
                                new OA\Property(property: 'type', type: 'string'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Expérience non trouvée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $command = new UpdateExperienceCommand(
                experienceId: (int) $id,
                data: $request->only(['title', 'description', 'short_description', 'price', 'status', 'type', 'images', 'location', 'tags', 'amenities'])
            );
            $experience = $this->updateExperienceHandler->handle($command);

            return ApiResponse::success(new ExperienceResource($experience), 'Expérience modifiée avec succès');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Supprimer une expérience
     */
    #[OA\Delete(
        path: '/api/v1/admin/experiences/{id}',
        summary: 'Supprimer une expérience',
        description: 'Supprime définitivement une expérience (fraude/violation)',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de l\'expérience',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Expérience supprimée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Expérience supprimée avec succès'),
                        new OA\Property(property: 'data', type: 'null'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Expérience non trouvée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $command = new DeleteExperienceCommand(experienceId: (int) $id);
        $deleted = $this->deleteExperienceHandler->handle($command);

        if (!$deleted) {
            return ApiResponse::error('Expérience non trouvée', 404);
        }

        return ApiResponse::success(null, 'Expérience supprimée avec succès');
    }

    /**
     * Modérer une expérience
     */
    #[OA\Put(
        path: '/api/v1/admin/experiences/{id}/moderate',
        summary: 'Modérer une expérience',
        description: 'Modère une expérience (approuver/rejeter)',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de l\'expérience',
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
                        enum: ['approved', 'rejected', 'suspended'],
                        example: 'approved'
                    ),
                    new OA\Property(property: 'reason', type: 'string', nullable: true, example: 'Contenu non conforme'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Expérience modérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Expérience modérée avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'uuid', type: 'string'),
                                new OA\Property(property: 'title', type: 'string'),
                                new OA\Property(property: 'status', type: 'string'),
                                new OA\Property(property: 'type', type: 'string'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Expérience non trouvée'),
            new OA\Response(response: 400, description: 'Statut invalide'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function moderate(Request $request, string $id): JsonResponse
    {
        try {
            $status = ExperienceStatus::from($request->input('status'));
            
            if (!in_array($status, [ExperienceStatus::APPROVED, ExperienceStatus::REJECTED, ExperienceStatus::SUSPENDED])) {
                return ApiResponse::error('Statut invalide pour la modération', 400);
            }

            $command = new ModerateExperienceCommand(
                experienceId: (int) $id,
                status: $status,
                reason: $request->input('reason')
            );
            $experience = $this->moderateExperienceHandler->handle($command);

            return ApiResponse::success(new ExperienceResource($experience), 'Expérience modérée avec succès');
        } catch (\ValueError $e) {
            return ApiResponse::error('Statut invalide', 400);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Expériences signalées par les utilisateurs
     */
    #[OA\Get(
        path: '/api/v1/admin/experiences/reports',
        summary: 'Expériences signalées par les utilisateurs',
        description: 'Récupère la liste des signalements d\'expériences',
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
                description: 'Liste des signalements',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'reason', type: 'string'),
                                    new OA\Property(property: 'description', type: 'string', nullable: true),
                                    new OA\Property(property: 'status', type: 'string'),
                                    new OA\Property(
                                        property: 'experience',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer'),
                                            new OA\Property(property: 'title', type: 'string'),
                                            new OA\Property(property: 'status', type: 'string'),
                                        ]
                                    ),
                                    new OA\Property(
                                        property: 'reporter',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer'),
                                            new OA\Property(property: 'name', type: 'string'),
                                            new OA\Property(property: 'email', type: 'string'),
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
    public function reports(Request $request): JsonResponse
    {
        $query = new GetExperienceReportsQuery(
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $reports = $this->getExperienceReportsHandler->handle($query);

        return ApiResponse::paginated($reports, 'Liste des signalements récupérée avec succès');
    }

    /**
     * Expériences en attente de validation
     */
    #[OA\Get(
        path: '/api/v1/admin/experiences/pending',
        summary: 'Expériences en attente de validation',
        description: 'Récupère la liste des expériences en attente de modération',
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
                description: 'Liste des expériences en attente',
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
                                    new OA\Property(property: 'title', type: 'string'),
                                    new OA\Property(property: 'status', type: 'string'),
                                    new OA\Property(property: 'type', type: 'string'),
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
    public function pending(Request $request): JsonResponse
    {
        $query = new GetPendingExperiencesQuery(
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $experiences = $this->getPendingExperiencesHandler->handle($query);

        return ApiResponse::paginated(
            $experiences,
            'Liste des expériences en attente récupérée avec succès',
            fn($exp) => new ExperienceResource($exp)
        );
    }
}


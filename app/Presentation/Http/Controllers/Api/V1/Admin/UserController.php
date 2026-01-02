<?php

namespace App\Presentation\Http\Controllers\Api\V1\Admin;

use App\Application\User\Commands\ActivateUserCommand;
use App\Application\User\Commands\DeleteUserCommand;
use App\Application\User\Commands\SuspendUserCommand;
use App\Application\User\Commands\ValidateProviderCommand;
use App\Application\User\Handlers\ActivateUserHandler;
use App\Application\User\Handlers\DeleteUserHandler;
use App\Application\User\Handlers\GetAllUsersHandler;
use App\Application\User\Handlers\GetUserStatisticsHandler;
use App\Application\User\Handlers\SuspendUserHandler;
use App\Application\User\Handlers\ValidateProviderHandler;
use App\Application\User\Queries\GetAllUsersQuery;
use App\Application\User\Queries\GetUserStatisticsQuery;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Resources\UserResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    public function __construct(
        private GetAllUsersHandler $getAllUsersHandler,
        private GetUserStatisticsHandler $getUserStatisticsHandler,
        private ActivateUserHandler $activateUserHandler,
        private SuspendUserHandler $suspendUserHandler,
        private ValidateProviderHandler $validateProviderHandler,
        private DeleteUserHandler $deleteUserHandler,
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Lister tous les utilisateurs
     */
    #[OA\Get(
        path: '/api/v1/admin/users',
        summary: 'Lister tous les utilisateurs',
        description: 'Récupère la liste paginée de tous les utilisateurs (voyageurs, prestataires) avec filtres optionnels',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'role',
                in: 'query',
                description: 'Filtrer par rôle',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['admin', 'traveler', 'provider', 'institution'])
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'Filtrer par statut',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'suspended', 'pending_verification', 'verified'])
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: 'Recherche par nom, email ou téléphone',
                required: false,
                schema: new OA\Schema(type: 'string')
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
                description: 'Liste des utilisateurs',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'uuid', type: 'string'),
                                    new OA\Property(property: 'name', type: 'string'),
                                    new OA\Property(property: 'email', type: 'string'),
                                    new OA\Property(property: 'role', type: 'string'),
                                    new OA\Property(property: 'status', type: 'string'),
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
        $query = new GetAllUsersQuery(
            role: $request->input('role'),
            status: $request->input('status'),
            search: $request->input('search'),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $users = $this->getAllUsersHandler->handle($query);

        return ApiResponse::paginated($users, 'Liste des utilisateurs récupérée avec succès');
    }

    /**
     * Consulter le profil détaillé d'un utilisateur
     */
    #[OA\Get(
        path: '/api/v1/admin/users/{id}',
        summary: 'Consulter le profil détaillé d\'un utilisateur',
        description: 'Récupère les informations complètes d\'un utilisateur par son ID',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de l\'utilisateur',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil utilisateur',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'uuid', type: 'string', example: '7514c633-716e-4d88-9ef5-46f8fc6dc714'),
                                new OA\Property(property: 'name', type: 'string', example: 'Amadou Diallo'),
                                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amadou.diallo@example.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'traveler'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            return ApiResponse::error('Utilisateur non trouvé', 404);
        }

        return ApiResponse::success(new UserResource($user), 'Profil utilisateur récupéré avec succès');
    }

    /**
     * Activer un compte utilisateur
     */
    #[OA\Put(
        path: '/api/v1/admin/users/{id}/activate',
        summary: 'Activer un compte utilisateur',
        description: 'Active un compte utilisateur (change le statut à active)',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de l\'utilisateur',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Compte activé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Compte activé avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'uuid', type: 'string', example: '7514c633-716e-4d88-9ef5-46f8fc6dc714'),
                                new OA\Property(property: 'name', type: 'string', example: 'Amadou Diallo'),
                                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amadou.diallo@example.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'traveler'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function activate(string $id): JsonResponse
    {
        try {
            $command = new ActivateUserCommand(userId: $id);
            $user = $this->activateUserHandler->handle($command);

            return ApiResponse::success(new UserResource($user), 'Compte activé avec succès');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Suspendre un compte utilisateur
     */
    #[OA\Put(
        path: '/api/v1/admin/users/{id}/suspend',
        summary: 'Suspendre un compte utilisateur',
        description: 'Suspend un compte utilisateur (change le statut à suspended)',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de l\'utilisateur',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'reason', type: 'string', nullable: true, example: 'Violation des conditions d\'utilisation'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Compte suspendu avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Compte suspendu avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'uuid', type: 'string', example: '7514c633-716e-4d88-9ef5-46f8fc6dc714'),
                                new OA\Property(property: 'name', type: 'string', example: 'Amadou Diallo'),
                                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amadou.diallo@example.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'traveler'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function suspend(Request $request, string $id): JsonResponse
    {
        try {
            $command = new SuspendUserCommand(
                userId: $id,
                reason: $request->input('reason')
            );
            $user = $this->suspendUserHandler->handle($command);

            return ApiResponse::success(new UserResource($user), 'Compte suspendu avec succès');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Valider un profil prestataire
     */
    #[OA\Put(
        path: '/api/v1/admin/users/{id}/validate',
        summary: 'Valider un profil prestataire',
        description: 'Valide le profil d\'un prestataire (change le statut à verified)',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de l\'utilisateur prestataire',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil validé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Profil validé avec succès'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'uuid', type: 'string', example: '7514c633-716e-4d88-9ef5-46f8fc6dc714'),
                                new OA\Property(property: 'name', type: 'string', example: 'Amadou Diallo'),
                                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amadou.diallo@example.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'traveler'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé'),
            new OA\Response(response: 400, description: 'L\'utilisateur n\'est pas un prestataire'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function validate(string $id): JsonResponse
    {
        try {
            $command = new ValidateProviderCommand(userId: $id);
            $user = $this->validateProviderHandler->handle($command);

            return ApiResponse::success(new UserResource($user), 'Profil validé avec succès');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Supprimer définitivement un utilisateur
     */
    #[OA\Delete(
        path: '/api/v1/admin/users/{id}',
        summary: 'Supprimer définitivement un utilisateur',
        description: 'Supprime définitivement un utilisateur de la base de données',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de l\'utilisateur',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur supprimé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Utilisateur supprimé avec succès'),
                        new OA\Property(property: 'data', type: 'null'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $command = new DeleteUserCommand(userId: $id);
        $deleted = $this->deleteUserHandler->handle($command);

        if (!$deleted) {
            return ApiResponse::error('Utilisateur non trouvé', 404);
        }

        return ApiResponse::success(null, 'Utilisateur supprimé avec succès');
    }

    /**
     * Statistiques des utilisateurs
     */
    #[OA\Get(
        path: '/api/v1/admin/users/statistics',
        summary: 'Statistiques des utilisateurs',
        description: 'Récupère les statistiques globales des utilisateurs (inscriptions, activité, répartition par rôle et statut)',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistiques des utilisateurs',
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
                                        new OA\Property(property: 'active', type: 'integer', example: 120),
                                        new OA\Property(property: 'pending_verification', type: 'integer', example: 20),
                                        new OA\Property(property: 'suspended', type: 'integer', example: 10),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'by_role',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'traveler', type: 'integer', example: 100),
                                        new OA\Property(property: 'provider', type: 'integer', example: 40),
                                        new OA\Property(property: 'admin', type: 'integer', example: 5),
                                        new OA\Property(property: 'institution', type: 'integer', example: 5),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'registrations',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'today', type: 'integer', example: 5),
                                        new OA\Property(property: 'this_week', type: 'integer', example: 25),
                                        new OA\Property(property: 'this_month', type: 'integer', example: 80),
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
        $query = new GetUserStatisticsQuery();
        $statistics = $this->getUserStatisticsHandler->handle($query);

        return ApiResponse::success($statistics, 'Statistiques récupérées avec succès');
    }
}


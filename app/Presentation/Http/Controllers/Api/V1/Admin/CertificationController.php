<?php

namespace App\Presentation\Http\Controllers\Api\V1\Admin;

use App\Application\Certification\Commands\CertifyProviderCommand;
use App\Application\Certification\Commands\CreateCertificationCommand;
use App\Application\Certification\Commands\RevokeCertificationCommand;
use App\Application\Certification\Commands\UpdateCertificationCommand;
use App\Application\Certification\Handlers\CertifyProviderHandler;
use App\Application\Certification\Handlers\CreateCertificationHandler;
use App\Application\Certification\Handlers\GetAllCertificationsHandler;
use App\Application\Certification\Handlers\GetCertificationByIdHandler;
use App\Application\Certification\Handlers\RevokeCertificationHandler;
use App\Application\Certification\Handlers\UpdateCertificationHandler;
use App\Application\Certification\Queries\GetAllCertificationsQuery;
use App\Application\Certification\Queries\GetCertificationByIdQuery;
use App\Infrastructure\Repositories\Contracts\CertificationRepositoryInterface;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Resources\CertificationResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CertificationController extends Controller
{
    public function __construct(
        private GetAllCertificationsHandler $getAllCertificationsHandler,
        private GetCertificationByIdHandler $getCertificationByIdHandler,
        private CreateCertificationHandler $createCertificationHandler,
        private UpdateCertificationHandler $updateCertificationHandler,
        private CertifyProviderHandler $certifyProviderHandler,
        private RevokeCertificationHandler $revokeCertificationHandler,
        private CertificationRepositoryInterface $certificationRepository
    ) {}

    /**
     * Lister toutes les certifications
     */
    #[OA\Get(
        path: '/api/v1/admin/certifications',
        summary: 'Lister toutes les certifications',
        description: 'Récupère la liste paginée de toutes les certifications disponibles',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'type', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'eco_responsible')),
            new OA\Parameter(name: 'is_active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des certifications'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = new GetAllCertificationsQuery(
            type: $request->input('type'),
            isActive: $request->input('is_active') !== null ? (bool) $request->input('is_active') : null,
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $certifications = $this->getAllCertificationsHandler->handle($query);

        return ApiResponse::paginated(
            $certifications,
            'Liste des certifications récupérée avec succès',
            fn($certification) => new CertificationResource($certification)
        );
    }

    /**
     * Créer une nouvelle certification
     */
    #[OA\Post(
        path: '/api/v1/admin/certifications',
        summary: 'Créer une nouvelle certification',
        description: 'Crée une nouvelle certification. Le badge doit être uploadé via Cloudinary et l\'URL stockée.',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name', 'description'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Certification Éco-Responsable'),
                        new OA\Property(property: 'description', type: 'string', example: 'Certification pour les prestataires respectueux de l\'environnement'),
                        new OA\Property(property: 'type', type: 'string', default: 'eco_responsible', example: 'eco_responsible'),
                        new OA\Property(property: 'badge_image', type: 'string', format: 'uri', nullable: true, description: 'URL Cloudinary du badge'),
                        new OA\Property(property: 'criteria', type: 'array', items: new OA\Items(type: 'string'), example: ['Réduction des déchets', 'Utilisation d\'énergies renouvelables']),
                        new OA\Property(property: 'validity_months', type: 'integer', nullable: true, example: 12),
                        new OA\Property(property: 'is_active', type: 'boolean', default: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Certification créée avec succès'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'nullable|string',
            'badge_image' => 'nullable|url',
            'criteria' => 'nullable|array',
            'criteria.*' => 'string',
            'validity_months' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $command = new CreateCertificationCommand(data: $validated);
        $certification = $this->createCertificationHandler->handle($command);

        return ApiResponse::success(new CertificationResource($certification), 'Certification créée avec succès', 201);
    }

    /**
     * Modifier une certification
     */
    #[OA\Put(
        path: '/api/v1/admin/certifications/{id}',
        summary: 'Modifier une certification',
        description: 'Modifie une certification existante. Le badge doit être uploadé via Cloudinary et l\'URL stockée.',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'description', type: 'string'),
                        new OA\Property(property: 'type', type: 'string'),
                        new OA\Property(property: 'badge_image', type: 'string', format: 'uri', nullable: true, description: 'URL Cloudinary du badge'),
                        new OA\Property(property: 'criteria', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'validity_months', type: 'integer'),
                        new OA\Property(property: 'is_active', type: 'boolean'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Certification modifiée avec succès'),
            new OA\Response(response: 404, description: 'Certification non trouvée'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'type' => 'nullable|string',
            'badge_image' => 'nullable|url',
            'criteria' => 'nullable|array',
            'criteria.*' => 'string',
            'validity_months' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $command = new UpdateCertificationCommand(certificationId: (int) $id, data: $validated);
            $certification = $this->updateCertificationHandler->handle($command);

            return ApiResponse::success(new CertificationResource($certification), 'Certification modifiée avec succès');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Attribuer certification éco-responsable
     */
    #[OA\Put(
        path: '/api/v1/admin/providers/{id}/certify',
        summary: 'Attribuer certification éco-responsable',
        description: 'Attribue une certification à un prestataire. Le certificat peut être uploadé via Cloudinary et l\'URL stockée.',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: 'ID du prestataire'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['certification_id'],
                    properties: [
                        new OA\Property(property: 'certification_id', type: 'integer', example: 1),
                        new OA\Property(property: 'issued_at', type: 'string', format: 'date', nullable: true, example: '2026-01-03'),
                        new OA\Property(property: 'validity_months', type: 'integer', nullable: true, example: 12),
                        new OA\Property(property: 'certificate_file', type: 'string', format: 'uri', nullable: true, description: 'URL Cloudinary du certificat'),
                        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Notes sur la certification'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Certification attribuée avec succès'),
            new OA\Response(response: 404, description: 'Prestataire ou certification non trouvé(e)'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function certifyProvider(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'certification_id' => 'required|integer|exists:certifications,id',
            'issued_at' => 'nullable|date',
            'validity_months' => 'nullable|integer|min:1',
            'certificate_file' => 'nullable|url',
            'notes' => 'nullable|string',
        ]);

        try {
            $command = new CertifyProviderCommand(
                providerId: (int) $id,
                certificationId: (int) $validated['certification_id'],
                data: $validated
            );
            $providerCertification = $this->certifyProviderHandler->handle($command);

            return ApiResponse::success($providerCertification, 'Certification attribuée avec succès');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Retirer une certification
     */
    #[OA\Delete(
        path: '/api/v1/admin/providers/{id}/certification',
        summary: 'Retirer une certification',
        description: 'Retire une certification d\'un prestataire',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: 'ID du prestataire'),
            new OA\Parameter(name: 'certification_id', in: 'query', required: true, schema: new OA\Schema(type: 'integer'), description: 'ID de la certification à retirer'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Certification retirée avec succès'),
            new OA\Response(response: 404, description: 'Prestataire ou certification non trouvé(e)'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function revokeCertification(Request $request, string $id): JsonResponse
    {
        $certificationId = $request->input('certification_id');
        
        if (!$certificationId) {
            return ApiResponse::error('certification_id est requis', 422);
        }

        $command = new RevokeCertificationCommand(
            providerId: (int) $id,
            certificationId: (int) $certificationId
        );
        $revoked = $this->revokeCertificationHandler->handle($command);

        if (!$revoked) {
            return ApiResponse::error('Prestataire ou certification non trouvé(e)', 404);
        }

        return ApiResponse::success(null, 'Certification retirée avec succès');
    }
}


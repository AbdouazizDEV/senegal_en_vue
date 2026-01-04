<?php

namespace App\Presentation\Http\Controllers\Api\V1\Admin;

use App\Application\Content\Commands\CreateBlogPostCommand;
use App\Application\Content\Commands\CreateHeritageStoryCommand;
use App\Application\Content\Commands\DeleteHeritageStoryCommand;
use App\Application\Content\Commands\UpdateHeritageStoryCommand;
use App\Application\Content\Handlers\CreateBlogPostHandler;
use App\Application\Content\Handlers\CreateHeritageStoryHandler;
use App\Application\Content\Handlers\DeleteHeritageStoryHandler;
use App\Application\Content\Handlers\GetAllBlogPostsHandler;
use App\Application\Content\Handlers\GetAllHeritageStoriesHandler;
use App\Application\Content\Handlers\GetHeritageStoryByIdHandler;
use App\Application\Content\Handlers\UpdateHeritageStoryHandler;
use App\Application\Content\Queries\GetAllBlogPostsQuery;
use App\Application\Content\Queries\GetAllHeritageStoriesQuery;
use App\Application\Content\Queries\GetHeritageStoryByIdQuery;
use App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Resources\BlogPostResource;
use App\Presentation\Http\Resources\HeritageStoryResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ContentController extends Controller
{
    public function __construct(
        private GetAllHeritageStoriesHandler $getAllHeritageStoriesHandler,
        private GetHeritageStoryByIdHandler $getHeritageStoryByIdHandler,
        private CreateHeritageStoryHandler $createHeritageStoryHandler,
        private UpdateHeritageStoryHandler $updateHeritageStoryHandler,
        private DeleteHeritageStoryHandler $deleteHeritageStoryHandler,
        private GetAllBlogPostsHandler $getAllBlogPostsHandler,
        private CreateBlogPostHandler $createBlogPostHandler,
        private ContentRepositoryInterface $contentRepository
    ) {}

    /**
     * Gérer "Histoires de nos anciens"
     */
    #[OA\Get(
        path: '/api/v1/admin/content/heritage-stories',
        summary: 'Gérer "Histoires de nos anciens"',
        description: 'Récupère la liste paginée de toutes les histoires du patrimoine',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['draft', 'published', 'archived'])),
            new OA\Parameter(name: 'is_featured', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des histoires'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function heritageStories(Request $request): JsonResponse
    {
        $query = new GetAllHeritageStoriesQuery(
            status: $request->input('status'),
            isFeatured: $request->input('is_featured') !== null ? (bool) $request->input('is_featured') : null,
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $stories = $this->getAllHeritageStoriesHandler->handle($query);

        return ApiResponse::paginated(
            $stories,
            'Liste des histoires récupérée avec succès',
            fn($story) => new HeritageStoryResource($story)
        );
    }

    /**
     * Ajouter une histoire
     */
    #[OA\Post(
        path: '/api/v1/admin/content/heritage-stories',
        summary: 'Ajouter une histoire',
        description: 'Crée une nouvelle histoire du patrimoine. Les images doivent être uploadées via Cloudinary et les URLs stockées.',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'content'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'L\'histoire de Gorée'),
                        new OA\Property(property: 'content', type: 'string', format: 'text', example: 'Contenu de l\'histoire...'),
                        new OA\Property(property: 'excerpt', type: 'string', nullable: true, example: 'Résumé de l\'histoire'),
                        new OA\Property(property: 'author_name', type: 'string', nullable: true, example: 'Amadou Diallo'),
                        new OA\Property(property: 'author_location', type: 'string', nullable: true, example: 'Dakar'),
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string', format: 'uri'), description: 'URLs Cloudinary des images'),
                        new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string'), example: ['patrimoine', 'histoire']),
                        new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'], default: 'draft'),
                        new OA\Property(property: 'is_featured', type: 'boolean', default: false),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Histoire créée avec succès'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function storeHeritageStory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'author_name' => 'nullable|string|max:255',
            'author_location' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'url',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'status' => 'nullable|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
        ]);

        $command = new CreateHeritageStoryCommand(data: $validated);
        $story = $this->createHeritageStoryHandler->handle($command);

        return ApiResponse::success(new HeritageStoryResource($story), 'Histoire créée avec succès', 201);
    }

    /**
     * Modifier une histoire
     */
    #[OA\Put(
        path: '/api/v1/admin/content/heritage-stories/{id}',
        summary: 'Modifier une histoire',
        description: 'Modifie une histoire existante. Les images doivent être uploadées via Cloudinary et les URLs stockées.',
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
                        new OA\Property(property: 'title', type: 'string', example: 'L\'histoire de Gorée'),
                        new OA\Property(property: 'content', type: 'string', format: 'text', example: 'Contenu de l\'histoire...'),
                        new OA\Property(property: 'excerpt', type: 'string', nullable: true),
                        new OA\Property(property: 'author_name', type: 'string', nullable: true),
                        new OA\Property(property: 'author_location', type: 'string', nullable: true),
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string', format: 'uri'), description: 'URLs Cloudinary des images'),
                        new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived']),
                        new OA\Property(property: 'is_featured', type: 'boolean'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Histoire modifiée avec succès'),
            new OA\Response(response: 404, description: 'Histoire non trouvée'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function updateHeritageStory(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'excerpt' => 'nullable|string',
            'author_name' => 'nullable|string|max:255',
            'author_location' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'url',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'status' => 'nullable|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
        ]);

        try {
            $command = new UpdateHeritageStoryCommand(storyId: (int) $id, data: $validated);
            $story = $this->updateHeritageStoryHandler->handle($command);

            return ApiResponse::success(new HeritageStoryResource($story), 'Histoire modifiée avec succès');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Supprimer une histoire
     */
    #[OA\Delete(
        path: '/api/v1/admin/content/heritage-stories/{id}',
        summary: 'Supprimer une histoire',
        description: 'Supprime définitivement une histoire',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Histoire supprimée avec succès'),
            new OA\Response(response: 404, description: 'Histoire non trouvée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function destroyHeritageStory(string $id): JsonResponse
    {
        $command = new DeleteHeritageStoryCommand(storyId: (int) $id);
        $deleted = $this->deleteHeritageStoryHandler->handle($command);

        if (!$deleted) {
            return ApiResponse::error('Histoire non trouvée', 404);
        }

        return ApiResponse::success(null, 'Histoire supprimée avec succès');
    }

    /**
     * Gérer les articles de blog
     */
    #[OA\Get(
        path: '/api/v1/admin/content/blog',
        summary: 'Gérer les articles de blog',
        description: 'Récupère la liste paginée de tous les articles de blog',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['draft', 'published', 'archived'])),
            new OA\Parameter(name: 'is_featured', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des articles de blog'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function blog(Request $request): JsonResponse
    {
        $query = new GetAllBlogPostsQuery(
            status: $request->input('status'),
            isFeatured: $request->input('is_featured') !== null ? (bool) $request->input('is_featured') : null,
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
        );

        $posts = $this->getAllBlogPostsHandler->handle($query);

        return ApiResponse::paginated(
            $posts,
            'Liste des articles de blog récupérée avec succès',
            fn($post) => new BlogPostResource($post)
        );
    }

    /**
     * Publier un article
     */
    #[OA\Post(
        path: '/api/v1/admin/content/blog',
        summary: 'Publier un article',
        description: 'Crée un nouvel article de blog. L\'image principale et les images doivent être uploadées via Cloudinary et les URLs stockées.',
        tags: ['Administration'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'content'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Découvrir le Sénégal'),
                        new OA\Property(property: 'content', type: 'string', format: 'text', example: 'Contenu de l\'article...'),
                        new OA\Property(property: 'excerpt', type: 'string', nullable: true, example: 'Résumé de l\'article'),
                        new OA\Property(property: 'featured_image', type: 'string', format: 'uri', nullable: true, description: 'URL Cloudinary de l\'image principale'),
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string', format: 'uri'), description: 'URLs Cloudinary des images'),
                        new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string'), example: ['tourisme', 'culture']),
                        new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'], default: 'draft'),
                        new OA\Property(property: 'is_featured', type: 'boolean', default: false),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Article créé avec succès'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Permissions insuffisantes'),
        ]
    )]
    public function storeBlogPost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|url',
            'images' => 'nullable|array',
            'images.*' => 'url',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'status' => 'nullable|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
        ]);

        $command = new CreateBlogPostCommand(data: $validated);
        $post = $this->createBlogPostHandler->handle($command);

        return ApiResponse::success(new BlogPostResource($post), 'Article créé avec succès', 201);
    }
}


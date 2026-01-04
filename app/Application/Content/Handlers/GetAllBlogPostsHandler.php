<?php

namespace App\Application\Content\Handlers;

use App\Application\Content\Queries\GetAllBlogPostsQuery;
use App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface;

class GetAllBlogPostsHandler
{
    public function __construct(
        private ContentRepositoryInterface $contentRepository
    ) {}

    public function handle(GetAllBlogPostsQuery $query)
    {
        $filters = array_filter([
            'status' => $query->status,
            'is_featured' => $query->isFeatured,
        ]);

        return $this->contentRepository->getAllBlogPosts($filters, $query->perPage);
    }
}


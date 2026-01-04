<?php

namespace App\Application\Content\Handlers;

use App\Application\Content\Queries\GetAllHeritageStoriesQuery;
use App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface;

class GetAllHeritageStoriesHandler
{
    public function __construct(
        private ContentRepositoryInterface $contentRepository
    ) {}

    public function handle(GetAllHeritageStoriesQuery $query)
    {
        $filters = array_filter([
            'status' => $query->status,
            'is_featured' => $query->isFeatured,
        ]);

        return $this->contentRepository->getAllHeritageStories($filters, $query->perPage);
    }
}


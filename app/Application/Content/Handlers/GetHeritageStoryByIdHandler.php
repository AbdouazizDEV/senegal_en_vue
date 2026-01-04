<?php

namespace App\Application\Content\Handlers;

use App\Application\Content\Queries\GetHeritageStoryByIdQuery;
use App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface;

class GetHeritageStoryByIdHandler
{
    public function __construct(
        private ContentRepositoryInterface $contentRepository
    ) {}

    public function handle(GetHeritageStoryByIdQuery $query)
    {
        return $this->contentRepository->findHeritageStoryById($query->storyId);
    }
}


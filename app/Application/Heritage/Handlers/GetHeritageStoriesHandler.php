<?php

namespace App\Application\Heritage\Handlers;

use App\Application\Heritage\Queries\GetHeritageStoriesQuery;
use App\Domain\Content\Models\HeritageStory;
use App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetHeritageStoriesHandler
{
    public function __construct(
        private ContentRepositoryInterface $contentRepository
    ) {}

    public function handle(GetHeritageStoriesQuery $query): LengthAwarePaginator
    {
        $filters = array_merge($query->filters, ['status' => 'published']);
        return $this->contentRepository->getAllHeritageStories($filters, $query->perPage);
    }
}


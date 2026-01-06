<?php

namespace App\Application\Heritage\Handlers;

use App\Application\Heritage\Queries\GetHeritageStoryQuery;
use App\Domain\Content\Models\HeritageStory;
use App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface;

class GetHeritageStoryHandler
{
    public function __construct(
        private ContentRepositoryInterface $contentRepository
    ) {}

    public function handle(GetHeritageStoryQuery $query): ?HeritageStory
    {
        $story = null;
        
        if (is_numeric($query->storyId)) {
            $story = $this->contentRepository->findHeritageStoryById($query->storyId);
        } else {
            // Si c'est un UUID, chercher directement
            $story = HeritageStory::where('uuid', $query->storyId)->with('creator')->first();
        }

        if (!$story || $story->status->value !== 'published') {
            return null;
        }

        // Incrémenter le compteur de vues
        $story->increment('views_count');

        return $story->fresh(['creator']);
    }
}


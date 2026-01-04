<?php

namespace App\Application\Content\Handlers;

use App\Application\Content\Commands\DeleteHeritageStoryCommand;
use App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface;

class DeleteHeritageStoryHandler
{
    public function __construct(
        private ContentRepositoryInterface $contentRepository
    ) {}

    public function handle(DeleteHeritageStoryCommand $command): bool
    {
        $story = $this->contentRepository->findHeritageStoryById($command->storyId);
        
        if (!$story) {
            return false;
        }

        return $this->contentRepository->deleteHeritageStory($story);
    }
}


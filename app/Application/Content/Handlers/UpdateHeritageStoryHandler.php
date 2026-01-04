<?php

namespace App\Application\Content\Handlers;

use App\Application\Content\Commands\UpdateHeritageStoryCommand;
use App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface;

class UpdateHeritageStoryHandler
{
    public function __construct(
        private ContentRepositoryInterface $contentRepository
    ) {}

    public function handle(UpdateHeritageStoryCommand $command)
    {
        $story = $this->contentRepository->findHeritageStoryById($command->storyId);
        
        if (!$story) {
            throw new \RuntimeException('Histoire non trouvée');
        }

        return $this->contentRepository->updateHeritageStory($story, $command->data);
    }
}


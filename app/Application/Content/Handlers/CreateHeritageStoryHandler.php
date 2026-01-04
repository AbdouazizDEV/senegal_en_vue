<?php

namespace App\Application\Content\Handlers;

use App\Application\Content\Commands\CreateHeritageStoryCommand;
use App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface;

class CreateHeritageStoryHandler
{
    public function __construct(
        private ContentRepositoryInterface $contentRepository
    ) {}

    public function handle(CreateHeritageStoryCommand $command)
    {
        $data = $command->data;
        $data['created_by'] = auth()->id();
        return $this->contentRepository->createHeritageStory($data);
    }
}


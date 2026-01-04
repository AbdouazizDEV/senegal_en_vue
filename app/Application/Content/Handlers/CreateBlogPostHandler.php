<?php

namespace App\Application\Content\Handlers;

use App\Application\Content\Commands\CreateBlogPostCommand;
use App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface;

class CreateBlogPostHandler
{
    public function __construct(
        private ContentRepositoryInterface $contentRepository
    ) {}

    public function handle(CreateBlogPostCommand $command)
    {
        $data = $command->data;
        $data['author_id'] = auth()->id();
        return $this->contentRepository->createBlogPost($data);
    }
}


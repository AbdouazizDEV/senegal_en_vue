<?php

namespace App\Application\Traveler\Message\Handlers;

use App\Application\Traveler\Message\Queries\GetUnreadMessagesQuery;
use App\Infrastructure\Repositories\Contracts\ConversationRepositoryInterface;

class GetUnreadMessagesHandler
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository
    ) {}

    public function handle(GetUnreadMessagesQuery $query): int
    {
        return $this->conversationRepository->getUnreadCount($query->travelerId);
    }
}



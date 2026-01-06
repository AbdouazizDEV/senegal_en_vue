<?php

namespace App\Application\Traveler\Message\Handlers;

use App\Application\Traveler\Message\Queries\GetConversationQuery;
use App\Domain\Message\Models\Conversation;
use App\Infrastructure\Repositories\Contracts\ConversationRepositoryInterface;

class GetConversationHandler
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository
    ) {}

    public function handle(GetConversationQuery $query): ?Conversation
    {
        $conversation = is_numeric($query->conversationId)
            ? $this->conversationRepository->findById($query->conversationId)
            : $this->conversationRepository->findByUuid($query->conversationId);

        if (!$conversation || $conversation->traveler_id !== $query->travelerId) {
            return null;
        }

        return $conversation;
    }
}



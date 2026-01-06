<?php

namespace App\Application\Traveler\Message\Handlers;

use App\Application\Traveler\Message\Queries\GetConversationsQuery;
use App\Infrastructure\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetConversationsHandler
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository
    ) {}

    public function handle(GetConversationsQuery $query): LengthAwarePaginator
    {
        return $this->conversationRepository->findByTravelerId(
            $query->travelerId,
            $query->filters,
            $query->perPage
        );
    }
}



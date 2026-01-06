<?php

namespace App\Application\Traveler\Message\Queries;

class GetConversationQuery
{
    public function __construct(
        public readonly int $travelerId,
        public readonly int|string $conversationId // Peut être ID ou UUID
    ) {}
}



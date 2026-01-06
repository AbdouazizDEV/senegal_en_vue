<?php

namespace App\Application\Traveler\Message\Queries;

class GetUnreadMessagesQuery
{
    public function __construct(
        public readonly int $travelerId
    ) {}
}



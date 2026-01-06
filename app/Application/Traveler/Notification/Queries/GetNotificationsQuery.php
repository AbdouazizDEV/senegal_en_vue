<?php

namespace App\Application\Traveler\Notification\Queries;

class GetNotificationsQuery
{
    public function __construct(
        public readonly int $userId,
        public readonly array $filters = [],
        public readonly int $perPage = 15
    ) {}
}



<?php

namespace App\Application\Traveler\Notification\Queries;

class GetUnreadNotificationsQuery
{
    public function __construct(
        public readonly int $userId
    ) {}
}



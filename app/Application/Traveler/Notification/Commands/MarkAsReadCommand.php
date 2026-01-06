<?php

namespace App\Application\Traveler\Notification\Commands;

class MarkAsReadCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly int $notificationId
    ) {}
}



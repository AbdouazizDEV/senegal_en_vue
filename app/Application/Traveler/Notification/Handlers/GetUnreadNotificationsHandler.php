<?php

namespace App\Application\Traveler\Notification\Handlers;

use App\Application\Traveler\Notification\Queries\GetUnreadNotificationsQuery;
use App\Infrastructure\Repositories\Contracts\NotificationRepositoryInterface;

class GetUnreadNotificationsHandler
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository
    ) {}

    public function handle(GetUnreadNotificationsQuery $query): \Illuminate\Database\Eloquent\Collection
    {
        return $this->notificationRepository->getUnread($query->userId, 50);
    }
}



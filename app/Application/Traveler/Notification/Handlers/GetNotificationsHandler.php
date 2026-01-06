<?php

namespace App\Application\Traveler\Notification\Handlers;

use App\Application\Traveler\Notification\Queries\GetNotificationsQuery;
use App\Infrastructure\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetNotificationsHandler
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository
    ) {}

    public function handle(GetNotificationsQuery $query): LengthAwarePaginator
    {
        return $this->notificationRepository->findByUserId(
            $query->userId,
            $query->filters,
            $query->perPage
        );
    }
}



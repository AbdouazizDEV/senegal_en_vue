<?php

namespace App\Application\Traveler\Notification\Handlers;

use App\Application\Traveler\Notification\Commands\MarkAsReadCommand;
use App\Domain\Notification\Models\Notification;
use App\Infrastructure\Repositories\Contracts\NotificationRepositoryInterface;

class MarkAsReadHandler
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository
    ) {}

    public function handle(MarkAsReadCommand $command): Notification
    {
        $notification = $this->notificationRepository->findById($command->notificationId);

        if (!$notification || $notification->user_id !== $command->userId) {
            throw new \Exception('Notification non trouvée ou accès non autorisé.');
        }

        return $this->notificationRepository->markAsRead($notification);
    }
}



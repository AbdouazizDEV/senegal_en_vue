<?php

namespace App\Application\Traveler\Notification\Handlers;

use App\Application\Traveler\Notification\Commands\UpdateNotificationSettingsCommand;
use App\Domain\Notification\Models\NotificationSetting;
use App\Infrastructure\Repositories\Contracts\NotificationSettingRepositoryInterface;

class UpdateNotificationSettingsHandler
{
    public function __construct(
        private NotificationSettingRepositoryInterface $notificationSettingRepository
    ) {}

    public function handle(UpdateNotificationSettingsCommand $command): NotificationSetting
    {
        $data = [];

        if ($command->emailEnabled !== null) {
            $data['email_enabled'] = $command->emailEnabled;
        }

        if ($command->smsEnabled !== null) {
            $data['sms_enabled'] = $command->smsEnabled;
        }

        if ($command->pushEnabled !== null) {
            $data['push_enabled'] = $command->pushEnabled;
        }

        if ($command->preferences !== null) {
            $data['preferences'] = $command->preferences;
        }

        return $this->notificationSettingRepository->createOrUpdate($command->userId, $data);
    }
}



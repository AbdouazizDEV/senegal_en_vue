<?php

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Notification\Models\NotificationSetting;
use App\Infrastructure\Repositories\Contracts\NotificationSettingRepositoryInterface;

class EloquentNotificationSettingRepository implements NotificationSettingRepositoryInterface
{
    public function findByUserId(int $userId): ?NotificationSetting
    {
        return NotificationSetting::where('user_id', $userId)->first();
    }

    public function createOrUpdate(int $userId, array $data): NotificationSetting
    {
        return NotificationSetting::updateOrCreate(
            ['user_id' => $userId],
            $data
        );
    }
}



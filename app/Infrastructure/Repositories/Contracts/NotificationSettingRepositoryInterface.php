<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Notification\Models\NotificationSetting;

interface NotificationSettingRepositoryInterface
{
    public function findByUserId(int $userId): ?NotificationSetting;
    
    public function createOrUpdate(int $userId, array $data): NotificationSetting;
}



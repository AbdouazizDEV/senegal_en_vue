<?php

namespace App\Application\Traveler\Notification\Commands;

class UpdateNotificationSettingsCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly ?bool $emailEnabled = null,
        public readonly ?bool $smsEnabled = null,
        public readonly ?bool $pushEnabled = null,
        public readonly ?array $preferences = null
    ) {}
}



<?php

namespace App\Application\Traveler\Message\Commands;

class ContactProviderCommand
{
    public function __construct(
        public readonly int $travelerId,
        public readonly int $providerId,
        public readonly ?int $experienceId = null,
        public readonly ?int $bookingId = null,
        public readonly ?string $subject = null,
        public readonly string $message,
        public readonly ?array $attachments = null
    ) {}
}



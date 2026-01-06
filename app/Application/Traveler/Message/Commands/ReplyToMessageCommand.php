<?php

namespace App\Application\Traveler\Message\Commands;

class ReplyToMessageCommand
{
    public function __construct(
        public readonly int $travelerId,
        public readonly int $conversationId,
        public readonly string $content,
        public readonly ?array $attachments = null
    ) {}
}



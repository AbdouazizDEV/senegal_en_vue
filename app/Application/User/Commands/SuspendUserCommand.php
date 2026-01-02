<?php

namespace App\Application\User\Commands;

readonly class SuspendUserCommand
{
    public function __construct(
        public string $userId,
        public ?string $reason = null,
    ) {}
}



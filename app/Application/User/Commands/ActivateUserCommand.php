<?php

namespace App\Application\User\Commands;

readonly class ActivateUserCommand
{
    public function __construct(
        public string $userId,
    ) {}
}



<?php

namespace App\Application\User\Commands;

readonly class DeleteUserCommand
{
    public function __construct(
        public string $userId,
    ) {}
}



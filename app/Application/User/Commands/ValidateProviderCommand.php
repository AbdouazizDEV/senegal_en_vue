<?php

namespace App\Application\User\Commands;

readonly class ValidateProviderCommand
{
    public function __construct(
        public string $userId,
    ) {}
}



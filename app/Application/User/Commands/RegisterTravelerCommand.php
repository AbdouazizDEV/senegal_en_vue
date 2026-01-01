<?php

namespace App\Application\User\Commands;

readonly class RegisterTravelerCommand
{
    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public string $password,
        public ?array $preferences = null,
    ) {}
}


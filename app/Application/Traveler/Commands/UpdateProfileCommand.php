<?php

namespace App\Application\Traveler\Commands;

readonly class UpdateProfileCommand
{
    public function __construct(
        public int $userId,
        public array $data
    ) {}
}


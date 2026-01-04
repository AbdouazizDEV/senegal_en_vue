<?php

namespace App\Application\Traveler\Commands;

readonly class UpdateProfilePhotoCommand
{
    public function __construct(
        public int $userId,
        public string $photoUrl
    ) {}
}


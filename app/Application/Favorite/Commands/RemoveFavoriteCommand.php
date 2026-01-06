<?php

namespace App\Application\Favorite\Commands;

readonly class RemoveFavoriteCommand
{
    public function __construct(
        public int $userId,
        public int $experienceId
    ) {}
}




<?php

namespace App\Application\Favorite\Commands;

readonly class AddFavoriteCommand
{
    public function __construct(
        public int $userId,
        public int $experienceId,
        public bool $notifyOnPriceDrop = false,
        public bool $notifyOnAvailability = false,
        public bool $notifyOnNewReviews = false,
    ) {}
}



<?php

namespace App\Application\Traveler\Review\Commands;

class CreateReviewCommand
{
    public function __construct(
        public readonly int $travelerId,
        public readonly int $bookingId,
        public readonly int $experienceId,
        public readonly int $providerId,
        public readonly int $rating, // 1-5
        public readonly string $comment,
        public readonly ?string $title = null,
        public readonly ?array $images = null
    ) {}
}


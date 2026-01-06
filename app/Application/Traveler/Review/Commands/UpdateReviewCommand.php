<?php

namespace App\Application\Traveler\Review\Commands;

class UpdateReviewCommand
{
    public function __construct(
        public readonly int $travelerId,
        public readonly int $reviewId,
        public readonly ?int $rating = null,
        public readonly ?string $title = null,
        public readonly ?string $comment = null,
        public readonly ?array $images = null
    ) {}
}



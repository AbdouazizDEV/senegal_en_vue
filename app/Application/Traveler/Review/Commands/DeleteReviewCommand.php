<?php

namespace App\Application\Traveler\Review\Commands;

class DeleteReviewCommand
{
    public function __construct(
        public readonly int $travelerId,
        public readonly int $reviewId
    ) {}
}



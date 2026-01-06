<?php

namespace App\Application\Traveler\Review\Commands;

class MarkHelpfulCommand
{
    public function __construct(
        public readonly int $reviewId
    ) {}
}


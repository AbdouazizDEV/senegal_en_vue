<?php

namespace App\Application\Traveler\Review\Queries;

class GetExperienceReviewsQuery
{
    public function __construct(
        public readonly int $experienceId,
        public readonly array $filters = [],
        public readonly int $perPage = 15
    ) {}
}



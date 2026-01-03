<?php

namespace App\Application\Review\Handlers;

use App\Application\Review\Queries\GetReviewStatisticsQuery;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;

class GetReviewStatisticsHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function handle(GetReviewStatisticsQuery $query): array
    {
        return $this->reviewRepository->getStatistics();
    }
}


<?php

namespace App\Application\Review\Handlers;

use App\Application\Review\Queries\GetReportedReviewsQuery;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;

class GetReportedReviewsHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function handle(GetReportedReviewsQuery $query)
    {
        return $this->reviewRepository->getReported($query->perPage);
    }
}


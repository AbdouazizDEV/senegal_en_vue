<?php

namespace App\Application\Review\Handlers;

use App\Application\Review\Queries\GetReviewByIdQuery;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;

class GetReviewByIdHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function handle(GetReviewByIdQuery $query)
    {
        return $this->reviewRepository->findById($query->reviewId);
    }
}


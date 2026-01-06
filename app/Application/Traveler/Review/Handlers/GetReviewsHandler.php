<?php

namespace App\Application\Traveler\Review\Handlers;

use App\Application\Traveler\Review\Queries\GetReviewsQuery;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetReviewsHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function handle(GetReviewsQuery $query): LengthAwarePaginator
    {
        return $this->reviewRepository->findByTravelerId(
            $query->travelerId,
            $query->filters,
            $query->perPage
        );
    }
}



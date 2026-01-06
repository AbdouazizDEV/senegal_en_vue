<?php

namespace App\Application\Traveler\Review\Handlers;

use App\Application\Traveler\Review\Queries\GetExperienceReviewsQuery;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetExperienceReviewsHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function handle(GetExperienceReviewsQuery $query): LengthAwarePaginator
    {
        return $this->reviewRepository->findByExperienceId(
            $query->experienceId,
            $query->filters,
            $query->perPage
        );
    }
}


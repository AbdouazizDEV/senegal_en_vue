<?php

namespace App\Application\Review\Handlers;

use App\Application\Review\Queries\GetAllReviewsQuery;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;

class GetAllReviewsHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function handle(GetAllReviewsQuery $query)
    {
        $filters = array_filter([
            'status' => $query->status,
            'experience_id' => $query->experienceId,
            'provider_id' => $query->providerId,
            'rating' => $query->rating,
            'is_verified' => $query->isVerified,
        ]);

        return $this->reviewRepository->getAll($filters, $query->perPage);
    }
}


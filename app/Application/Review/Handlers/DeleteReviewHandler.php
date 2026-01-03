<?php

namespace App\Application\Review\Handlers;

use App\Application\Review\Commands\DeleteReviewCommand;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;

class DeleteReviewHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function handle(DeleteReviewCommand $command): bool
    {
        $review = $this->reviewRepository->findById($command->reviewId);
        
        if (!$review) {
            return false;
        }

        return $this->reviewRepository->delete($review);
    }
}


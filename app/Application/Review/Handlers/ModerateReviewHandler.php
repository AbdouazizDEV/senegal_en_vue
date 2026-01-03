<?php

namespace App\Application\Review\Handlers;

use App\Application\Review\Commands\ModerateReviewCommand;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;

class ModerateReviewHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function handle(ModerateReviewCommand $command)
    {
        $review = $this->reviewRepository->findById($command->reviewId);
        
        if (!$review) {
            throw new \RuntimeException('Avis non trouvé');
        }

        return $this->reviewRepository->moderate($review, $command->status, $command->reason);
    }
}


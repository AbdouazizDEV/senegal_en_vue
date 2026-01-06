<?php

namespace App\Application\Traveler\Review\Handlers;

use App\Application\Traveler\Review\Commands\MarkHelpfulCommand;
use App\Domain\Review\Models\Review;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;

class MarkHelpfulHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function handle(MarkHelpfulCommand $command): Review
    {
        $review = $this->reviewRepository->findById($command->reviewId);

        if (!$review) {
            throw new \Exception('Avis non trouvé.');
        }

        return $this->reviewRepository->incrementHelpfulCount($review);
    }
}


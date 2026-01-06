<?php

namespace App\Application\Traveler\Review\Handlers;

use App\Application\Traveler\Review\Commands\DeleteReviewCommand;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;

class DeleteReviewHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function handle(DeleteReviewCommand $command): bool
    {
        $review = $this->reviewRepository->findById($command->reviewId);

        if (!$review || $review->traveler_id !== $command->travelerId) {
            throw new \Exception('Avis non trouvé ou accès non autorisé.');
        }

        return $this->reviewRepository->delete($review);
    }
}



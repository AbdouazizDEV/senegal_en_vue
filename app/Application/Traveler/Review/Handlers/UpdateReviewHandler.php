<?php

namespace App\Application\Traveler\Review\Handlers;

use App\Application\Traveler\Review\Commands\UpdateReviewCommand;
use App\Domain\Review\Models\Review;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;

class UpdateReviewHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function handle(UpdateReviewCommand $command): Review
    {
        $review = $this->reviewRepository->findById($command->reviewId);

        if (!$review || $review->traveler_id !== $command->travelerId) {
            throw new \Exception('Avis non trouvé ou accès non autorisé.');
        }

        // Ne peut modifier que les avis en attente ou approuvés
        if (!in_array($review->status->value, ['pending', 'approved'])) {
            throw new \Exception('Cet avis ne peut plus être modifié.');
        }

        $data = [];

        if ($command->rating !== null) {
            $data['rating'] = $command->rating;
        }

        if ($command->title !== null) {
            $data['title'] = $command->title;
        }

        if ($command->comment !== null) {
            $data['comment'] = $command->comment;
        }

        if ($command->images !== null) {
            $data['images'] = $command->images;
        }

        // Si l'avis était approuvé, le remettre en attente après modification
        if ($review->status->value === 'approved') {
            $data['status'] = \App\Domain\Review\Enums\ReviewStatus::PENDING;
            $data['approved_at'] = null;
        }

        return $this->reviewRepository->update($review, $data);
    }
}


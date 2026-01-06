<?php

namespace App\Application\Traveler\Review\Handlers;

use App\Application\Traveler\Review\Commands\CreateReviewCommand;
use App\Domain\Review\Enums\ReviewStatus;
use App\Domain\Review\Models\Review;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;

class CreateReviewHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository,
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(CreateReviewCommand $command): Review
    {
        // Vérifier que la réservation existe et appartient au voyageur
        $booking = $this->bookingRepository->findById($command->bookingId);

        if (!$booking || $booking->traveler_id !== $command->travelerId) {
            throw new \Exception('Réservation non trouvée ou accès non autorisé.');
        }

        // Vérifier qu'un avis n'existe pas déjà pour cette réservation
        $existingReview = Review::where('booking_id', $command->bookingId)
            ->where('traveler_id', $command->travelerId)
            ->first();

        if ($existingReview) {
            throw new \Exception('Un avis existe déjà pour cette réservation.');
        }

        // Vérifier que la réservation est complétée ou confirmée
        if (!in_array($booking->status->value, ['confirmed', 'completed'])) {
            throw new \Exception('Vous ne pouvez laisser un avis que pour une réservation confirmée ou complétée.');
        }

        $data = [
            'booking_id' => $command->bookingId,
            'experience_id' => $command->experienceId,
            'traveler_id' => $command->travelerId,
            'provider_id' => $command->providerId,
            'rating' => $command->rating,
            'comment' => $command->comment,
            'status' => ReviewStatus::PENDING,
            'is_verified' => $booking->status->value === 'completed',
        ];

        if ($command->title) {
            $data['title'] = $command->title;
        }

        if ($command->images) {
            $data['images'] = $command->images;
        }

        return $this->reviewRepository->create($data);
    }
}


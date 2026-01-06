<?php

namespace App\Application\Traveler\Booking\Handlers;

use App\Application\Traveler\Booking\Commands\CreateBookingCommand;
use App\Domain\Booking\Enums\BookingStatus;
use App\Domain\Booking\Enums\PaymentStatus;
use App\Domain\Booking\Models\Booking;
use App\Domain\Experience\Models\Experience;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class CreateBookingHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository,
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(CreateBookingCommand $command): Booking
    {
        // Vérifier que l'expérience existe et est approuvée
        $experience = $this->experienceRepository->findById($command->experienceId);
        
        if (!$experience) {
            throw new \Exception('Expérience non trouvée.');
        }

        if ($experience->status !== \App\Domain\Experience\Enums\ExperienceStatus::APPROVED) {
            throw new \Exception('Cette expérience n\'est pas disponible pour la réservation.');
        }

        // Vérifier la disponibilité
        $bookingDate = new \DateTime($command->bookingDate);
        $isAvailable = $this->experienceRepository->checkAvailability(
            $command->experienceId,
            $bookingDate,
            $command->participantsCount
        );

        if (!$isAvailable) {
            throw new \Exception('L\'expérience n\'est pas disponible pour cette date et ce nombre de participants.');
        }

        // Vérifier les limites de participants
        if ($experience->min_participants && $command->participantsCount < $experience->min_participants) {
            throw new \Exception("Le nombre minimum de participants requis est {$experience->min_participants}.");
        }

        if ($experience->max_participants && $command->participantsCount > $experience->max_participants) {
            throw new \Exception("Le nombre maximum de participants autorisé est {$experience->max_participants}.");
        }

        // Calculer le montant total
        $totalAmount = $experience->price * $command->participantsCount;

        // Créer la réservation
        $bookingData = [
            'experience_id' => $command->experienceId,
            'traveler_id' => $command->travelerId,
            'provider_id' => $experience->provider_id,
            'status' => BookingStatus::PENDING,
            'booking_date' => $command->bookingDate,
            'booking_time' => $command->bookingTime,
            'participants_count' => $command->participantsCount,
            'total_amount' => $totalAmount,
            'currency' => $experience->currency ?? 'XOF',
            'payment_status' => PaymentStatus::PENDING,
            'payment_method' => $command->paymentMethod,
            'special_requests' => $command->specialRequests,
            'metadata' => $command->metadata,
        ];

        return $this->bookingRepository->create($bookingData);
    }
}




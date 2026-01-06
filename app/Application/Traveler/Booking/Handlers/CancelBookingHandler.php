<?php

namespace App\Application\Traveler\Booking\Handlers;

use App\Application\Traveler\Booking\Commands\CancelBookingCommand;
use App\Domain\Booking\Enums\BookingStatus;
use App\Domain\Booking\Models\Booking;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;

class CancelBookingHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(CancelBookingCommand $command): Booking
    {
        $booking = $this->bookingRepository->findById($command->bookingId);

        if (!$booking) {
            throw new \Exception('Réservation non trouvée.');
        }

        if ($booking->traveler_id !== $command->travelerId) {
            throw new \Exception('Vous n\'êtes pas autorisé à annuler cette réservation.');
        }

        if (!$booking->status->canBeCancelled()) {
            throw new \Exception('Cette réservation ne peut pas être annulée.');
        }

        return $this->bookingRepository->cancel(
            $booking,
            $command->reason,
            $command->travelerId
        );
    }
}



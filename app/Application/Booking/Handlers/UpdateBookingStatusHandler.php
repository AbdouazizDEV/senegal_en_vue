<?php

namespace App\Application\Booking\Handlers;

use App\Application\Booking\Commands\UpdateBookingStatusCommand;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;

class UpdateBookingStatusHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(UpdateBookingStatusCommand $command)
    {
        $booking = $this->bookingRepository->findById($command->bookingId);
        
        if (!$booking) {
            throw new \RuntimeException('Réservation non trouvée');
        }

        return $this->bookingRepository->updateStatus($booking, $command->status, $command->reason);
    }
}


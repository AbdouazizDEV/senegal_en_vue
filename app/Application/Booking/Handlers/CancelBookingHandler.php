<?php

namespace App\Application\Booking\Handlers;

use App\Application\Booking\Commands\CancelBookingCommand;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;

class CancelBookingHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(CancelBookingCommand $command)
    {
        $booking = $this->bookingRepository->findById($command->bookingId);
        
        if (!$booking) {
            throw new \RuntimeException('Réservation non trouvée');
        }

        return $this->bookingRepository->cancel($booking, $command->reason, $command->cancelledBy);
    }
}


<?php

namespace App\Application\Traveler\Booking\Handlers;

use App\Application\Traveler\Booking\Queries\GetBookingByIdQuery;
use App\Domain\Booking\Models\Booking;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;

class GetBookingByIdHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(GetBookingByIdQuery $query): ?Booking
    {
        $booking = $this->bookingRepository->findById($query->bookingId);

        if (!$booking || $booking->traveler_id !== $query->travelerId) {
            return null;
        }

        return $booking;
    }
}




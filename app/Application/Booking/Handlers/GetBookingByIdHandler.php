<?php

namespace App\Application\Booking\Handlers;

use App\Application\Booking\Queries\GetBookingByIdQuery;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;

class GetBookingByIdHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(GetBookingByIdQuery $query)
    {
        return $this->bookingRepository->findById($query->bookingId);
    }
}


<?php

namespace App\Application\Booking\Handlers;

use App\Application\Booking\Queries\GetBookingDisputesQuery;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;

class GetBookingDisputesHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(GetBookingDisputesQuery $query)
    {
        return $this->bookingRepository->getDisputes($query->perPage);
    }
}


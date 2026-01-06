<?php

namespace App\Application\Traveler\Booking\Handlers;

use App\Application\Traveler\Booking\Queries\GetPendingBookingsQuery;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetPendingBookingsHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(GetPendingBookingsQuery $query): LengthAwarePaginator
    {
        return $this->bookingRepository->getPendingByTraveler(
            $query->travelerId,
            $query->perPage
        );
    }
}



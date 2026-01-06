<?php

namespace App\Application\Traveler\Booking\Handlers;

use App\Application\Traveler\Booking\Queries\GetUpcomingBookingsQuery;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetUpcomingBookingsHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(GetUpcomingBookingsQuery $query): LengthAwarePaginator
    {
        return $this->bookingRepository->getUpcomingByTraveler(
            $query->travelerId,
            $query->perPage
        );
    }
}




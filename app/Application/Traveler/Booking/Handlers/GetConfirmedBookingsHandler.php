<?php

namespace App\Application\Traveler\Booking\Handlers;

use App\Application\Traveler\Booking\Queries\GetConfirmedBookingsQuery;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetConfirmedBookingsHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(GetConfirmedBookingsQuery $query): LengthAwarePaginator
    {
        return $this->bookingRepository->getConfirmedByTraveler(
            $query->travelerId,
            $query->perPage
        );
    }
}




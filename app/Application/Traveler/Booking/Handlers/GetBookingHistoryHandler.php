<?php

namespace App\Application\Traveler\Booking\Handlers;

use App\Application\Traveler\Booking\Queries\GetBookingHistoryQuery;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetBookingHistoryHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(GetBookingHistoryQuery $query): LengthAwarePaginator
    {
        return $this->bookingRepository->getHistoryByTraveler(
            $query->travelerId,
            $query->perPage
        );
    }
}




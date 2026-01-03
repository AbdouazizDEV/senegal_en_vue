<?php

namespace App\Application\Booking\Handlers;

use App\Application\Booking\Queries\GetBookingStatisticsQuery;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;

class GetBookingStatisticsHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(GetBookingStatisticsQuery $query): array
    {
        return $this->bookingRepository->getStatistics();
    }
}


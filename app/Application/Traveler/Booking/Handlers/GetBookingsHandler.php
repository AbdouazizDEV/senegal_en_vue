<?php

namespace App\Application\Traveler\Booking\Handlers;

use App\Application\Traveler\Booking\Queries\GetBookingsQuery;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetBookingsHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(GetBookingsQuery $query): LengthAwarePaginator
    {
        $filters = array_filter([
            'status' => $query->status,
            'payment_status' => $query->paymentStatus,
            'date_from' => $query->dateFrom,
            'date_to' => $query->dateTo,
        ], fn($value) => $value !== null);

        return $this->bookingRepository->findByTraveler(
            $query->travelerId,
            $filters,
            $query->perPage
        );
    }
}




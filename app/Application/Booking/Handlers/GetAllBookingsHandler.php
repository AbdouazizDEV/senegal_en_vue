<?php

namespace App\Application\Booking\Handlers;

use App\Application\Booking\Queries\GetAllBookingsQuery;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;

class GetAllBookingsHandler
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(GetAllBookingsQuery $query)
    {
        $filters = array_filter([
            'status' => $query->status,
            'payment_status' => $query->paymentStatus,
            'experience_id' => $query->experienceId,
            'traveler_id' => $query->travelerId,
            'provider_id' => $query->providerId,
            'date_from' => $query->dateFrom,
            'date_to' => $query->dateTo,
        ]);

        return $this->bookingRepository->getAll($filters, $query->perPage);
    }
}


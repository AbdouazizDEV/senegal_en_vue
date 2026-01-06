<?php

namespace App\Application\Traveler\Booking\Queries;

readonly class GetBookingsQuery
{
    public function __construct(
        public int $travelerId,
        public ?string $status = null,
        public ?string $paymentStatus = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public int $page = 1,
        public int $perPage = 15
    ) {}
}



<?php

namespace App\Application\Booking\Queries;

readonly class GetBookingDisputesQuery
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}


<?php

namespace App\Application\Booking\Queries;

readonly class GetBookingByIdQuery
{
    public function __construct(
        public int $bookingId
    ) {}
}


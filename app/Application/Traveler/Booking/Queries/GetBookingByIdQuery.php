<?php

namespace App\Application\Traveler\Booking\Queries;

readonly class GetBookingByIdQuery
{
    public function __construct(
        public int $bookingId,
        public int $travelerId
    ) {}
}




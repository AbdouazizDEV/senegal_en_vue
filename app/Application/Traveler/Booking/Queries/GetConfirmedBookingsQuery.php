<?php

namespace App\Application\Traveler\Booking\Queries;

readonly class GetConfirmedBookingsQuery
{
    public function __construct(
        public int $travelerId,
        public int $page = 1,
        public int $perPage = 15
    ) {}
}




<?php

namespace App\Application\Traveler\Booking\Commands;

readonly class CancelBookingCommand
{
    public function __construct(
        public int $bookingId,
        public int $travelerId,
        public ?string $reason = null
    ) {}
}




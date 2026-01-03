<?php

namespace App\Application\Booking\Commands;

readonly class CancelBookingCommand
{
    public function __construct(
        public int $bookingId,
        public ?string $reason = null,
        public ?int $cancelledBy = null
    ) {}
}


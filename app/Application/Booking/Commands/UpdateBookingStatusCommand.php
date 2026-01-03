<?php

namespace App\Application\Booking\Commands;

use App\Domain\Booking\Enums\BookingStatus;

readonly class UpdateBookingStatusCommand
{
    public function __construct(
        public int $bookingId,
        public BookingStatus $status,
        public ?string $reason = null
    ) {}
}


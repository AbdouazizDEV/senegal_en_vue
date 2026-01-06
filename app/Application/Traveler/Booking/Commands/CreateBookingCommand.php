<?php

namespace App\Application\Traveler\Booking\Commands;

readonly class CreateBookingCommand
{
    public function __construct(
        public int $travelerId,
        public int $experienceId,
        public string $bookingDate,
        public ?string $bookingTime = null,
        public int $participantsCount = 1,
        public ?string $specialRequests = null,
        public ?string $paymentMethod = null,
        public ?array $metadata = null
    ) {}
}




<?php

namespace App\Application\Booking\Queries;

readonly class GetAllBookingsQuery
{
    public function __construct(
        public ?string $status = null,
        public ?string $paymentStatus = null,
        public ?int $experienceId = null,
        public ?int $travelerId = null,
        public ?int $providerId = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}


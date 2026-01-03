<?php
namespace App\Application\Payment\Queries;
readonly class GetAllPaymentsQuery {
    public function __construct(
        public ?string $status = null,
        public ?string $type = null,
        public ?int $bookingId = null,
        public ?int $providerId = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}

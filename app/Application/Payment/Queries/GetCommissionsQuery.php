<?php
namespace App\Application\Payment\Queries;
readonly class GetCommissionsQuery {
    public function __construct(
        public ?int $providerId = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}

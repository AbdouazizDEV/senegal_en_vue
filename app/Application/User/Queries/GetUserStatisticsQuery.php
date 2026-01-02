<?php

namespace App\Application\User\Queries;

readonly class GetUserStatisticsQuery
{
    public function __construct(
        public ?string $startDate = null,
        public ?string $endDate = null,
    ) {}
}



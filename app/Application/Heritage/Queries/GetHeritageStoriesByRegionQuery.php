<?php

namespace App\Application\Heritage\Queries;

class GetHeritageStoriesByRegionQuery
{
    public function __construct(
        public readonly string $region,
        public readonly int $perPage = 15
    ) {}
}



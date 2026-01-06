<?php

namespace App\Application\Heritage\Queries;

class GetHeritageStoriesQuery
{
    public function __construct(
        public readonly array $filters = [],
        public readonly int $perPage = 15
    ) {}
}



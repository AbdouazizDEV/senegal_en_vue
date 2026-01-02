<?php

namespace App\Application\Experience\Queries;

readonly class GetPendingExperiencesQuery
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}


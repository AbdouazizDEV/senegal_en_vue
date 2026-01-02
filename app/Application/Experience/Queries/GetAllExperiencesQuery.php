<?php

namespace App\Application\Experience\Queries;

readonly class GetAllExperiencesQuery
{
    public function __construct(
        public ?string $status = null,
        public ?string $type = null,
        public ?int $providerId = null,
        public ?string $search = null,
        public ?bool $isFeatured = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}


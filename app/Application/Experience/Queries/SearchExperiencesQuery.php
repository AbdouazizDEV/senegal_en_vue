<?php
namespace App\Application\Experience\Queries;
readonly class SearchExperiencesQuery {
    public function __construct(
        public ?string $search = null,
        public ?string $type = null,
        public ?string $region = null,
        public ?string $city = null,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public ?array $tags = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}

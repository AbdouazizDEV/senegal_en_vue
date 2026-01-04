<?php
namespace App\Application\Experience\Queries;
readonly class GetExperiencesByPriceQuery {
    public function __construct(
        public float $minPrice,
        public float $maxPrice,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}

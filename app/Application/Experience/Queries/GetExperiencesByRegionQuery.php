<?php
namespace App\Application\Experience\Queries;
readonly class GetExperiencesByRegionQuery {
    public function __construct(
        public string $region,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}

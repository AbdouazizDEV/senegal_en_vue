<?php
namespace App\Application\Experience\Queries;
readonly class GetFeaturedExperiencesQuery {
    public function __construct(public int $limit = 10) {}
}

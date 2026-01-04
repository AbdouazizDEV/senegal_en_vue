<?php
namespace App\Application\Experience\Queries;
readonly class GetSimilarExperiencesQuery {
    public function __construct(
        public int $experienceId,
        public int $limit = 5
    ) {}
}

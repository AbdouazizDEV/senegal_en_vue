<?php
namespace App\Application\Experience\Queries;
readonly class GetRecentExperiencesQuery {
    public function __construct(public int $limit = 10) {}
}

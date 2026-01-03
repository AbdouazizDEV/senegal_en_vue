<?php
namespace App\Application\Review\Queries;
readonly class GetReportedReviewsQuery {
    public function __construct(public int $page = 1, public int $perPage = 15) {}
}

<?php
namespace App\Application\Review\Queries;
readonly class GetReviewByIdQuery {
    public function __construct(public int $reviewId) {}
}

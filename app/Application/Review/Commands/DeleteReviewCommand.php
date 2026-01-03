<?php
namespace App\Application\Review\Commands;
readonly class DeleteReviewCommand {
    public function __construct(public int $reviewId) {}
}

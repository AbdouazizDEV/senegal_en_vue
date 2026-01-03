<?php
namespace App\Application\Review\Commands;
use App\Domain\Review\Enums\ReviewStatus;
readonly class ModerateReviewCommand {
    public function __construct(
        public int $reviewId,
        public ReviewStatus $status,
        public ?string $reason = null
    ) {}
}

<?php
namespace App\Application\Review\Queries;
readonly class GetAllReviewsQuery {
    public function __construct(
        public ?string $status = null,
        public ?int $experienceId = null,
        public ?int $providerId = null,
        public ?int $rating = null,
        public ?bool $isVerified = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}

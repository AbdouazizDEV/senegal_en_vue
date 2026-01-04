<?php
namespace App\Application\Content\Queries;
readonly class GetAllHeritageStoriesQuery {
    public function __construct(
        public ?string $status = null,
        public ?bool $isFeatured = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}

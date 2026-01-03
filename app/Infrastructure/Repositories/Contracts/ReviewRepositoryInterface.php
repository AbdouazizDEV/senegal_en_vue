<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Review\Enums\ReviewStatus;
use App\Domain\Review\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface
{
    public function findById(int $id): ?Review;
    
    public function findByUuid(string $uuid): ?Review;
    
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function getReported(int $perPage = 15): LengthAwarePaginator;
    
    public function moderate(Review $review, ReviewStatus $status, ?string $reason = null): Review;
    
    public function delete(Review $review): bool;
    
    public function getStatistics(): array;
}


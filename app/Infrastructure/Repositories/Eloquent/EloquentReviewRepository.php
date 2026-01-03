<?php

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Review\Enums\ReviewStatus;
use App\Domain\Review\Models\Review;
use App\Infrastructure\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentReviewRepository implements ReviewRepositoryInterface
{
    public function findById(int $id): ?Review
    {
        return Review::with(['booking', 'experience', 'traveler', 'provider', 'reports'])->find($id);
    }

    public function findByUuid(string $uuid): ?Review
    {
        return Review::with(['booking', 'experience', 'traveler', 'provider', 'reports'])
            ->where('uuid', $uuid)
            ->first();
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Review::with(['experience', 'traveler', 'provider']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['experience_id'])) {
            $query->where('experience_id', $filters['experience_id']);
        }

        if (isset($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (isset($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        if (isset($filters['is_verified'])) {
            $query->where('is_verified', $filters['is_verified']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getReported(int $perPage = 15): LengthAwarePaginator
    {
        return Review::with(['experience', 'traveler', 'provider', 'reports.reporter'])
            ->reported()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function moderate(Review $review, ReviewStatus $status, ?string $reason = null): Review
    {
        $data = ['status' => $status];

        if ($status === ReviewStatus::APPROVED) {
            $data['approved_at'] = now();
        } elseif ($status === ReviewStatus::REJECTED) {
            $data['rejected_at'] = now();
            if ($reason) {
                $data['rejection_reason'] = $reason;
            }
        }

        $review->update($data);
        return $review->fresh(['experience', 'traveler', 'provider']);
    }

    public function delete(Review $review): bool
    {
        return $review->delete();
    }

    public function getStatistics(): array
    {
        $total = Review::count();
        $approved = Review::where('status', ReviewStatus::APPROVED)->count();
        $reported = Review::where('status', ReviewStatus::REPORTED)->count();
        
        $averageRating = Review::where('status', ReviewStatus::APPROVED)->avg('rating');
        
        $byRating = Review::where('status', ReviewStatus::APPROVED)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $today = Review::whereDate('created_at', today())->count();
        $thisWeek = Review::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth = Review::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'total' => $total,
            'approved' => $approved,
            'reported' => $reported,
            'average_rating' => round($averageRating, 2),
            'by_rating' => $byRating,
            'reviews' => [
                'today' => $today,
                'this_week' => $thisWeek,
                'this_month' => $thisMonth,
            ],
        ];
    }
}


<?php

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Favorite\Models\Favorite;
use App\Infrastructure\Repositories\Contracts\FavoriteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentFavoriteRepository implements FavoriteRepositoryInterface
{
    public function findByUser(int $userId, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return Favorite::where('user_id', $userId)
            ->with(['experience.provider'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findByUserAndExperience(int $userId, int $experienceId): ?Favorite
    {
        return Favorite::where('user_id', $userId)
            ->where('experience_id', $experienceId)
            ->first();
    }

    public function create(int $userId, int $experienceId, array $notificationPreferences = []): Favorite
    {
        return Favorite::create([
            'user_id' => $userId,
            'experience_id' => $experienceId,
            'notify_on_price_drop' => $notificationPreferences['notify_on_price_drop'] ?? false,
            'notify_on_availability' => $notificationPreferences['notify_on_availability'] ?? false,
            'notify_on_new_reviews' => $notificationPreferences['notify_on_new_reviews'] ?? false,
        ]);
    }

    public function delete(int $userId, int $experienceId): bool
    {
        return Favorite::where('user_id', $userId)
            ->where('experience_id', $experienceId)
            ->delete() > 0;
    }

    public function getUserFavoritesCount(int $userId): int
    {
        return Favorite::where('user_id', $userId)->count();
    }

    public function getFavoritesWithAlerts(int $userId): Collection
    {
        return Favorite::where('user_id', $userId)
            ->where(function ($query) {
                $query->where('notify_on_price_drop', true)
                    ->orWhere('notify_on_availability', true)
                    ->orWhere('notify_on_new_reviews', true);
            })
            ->with(['experience'])
            ->get();
    }
}


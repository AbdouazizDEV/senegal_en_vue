<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Favorite\Models\Favorite;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface FavoriteRepositoryInterface
{
    public function findByUser(int $userId, int $page = 1, int $perPage = 15): LengthAwarePaginator;
    
    public function findByUserAndExperience(int $userId, int $experienceId): ?Favorite;
    
    public function create(int $userId, int $experienceId, array $notificationPreferences = []): Favorite;
    
    public function delete(int $userId, int $experienceId): bool;
    
    public function getUserFavoritesCount(int $userId): int;
    
    public function getFavoritesWithAlerts(int $userId): Collection;
}



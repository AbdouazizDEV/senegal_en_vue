<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Discovery\Models\DiscoveryPreference;
use Illuminate\Database\Eloquent\Collection;

interface DiscoveryRepositoryInterface
{
    public function findPreferencesByUser(int $userId): ?DiscoveryPreference;
    
    public function createOrUpdatePreferences(int $userId, array $preferences): DiscoveryPreference;
    
    public function getPersonalizedSuggestions(int $userId, int $limit = 10): Collection;
    
    public function getTrendingExperiences(int $limit = 10): Collection;
    
    public function getHiddenGems(int $userId, int $limit = 10): Collection;
}




<?php

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Discovery\Models\DiscoveryPreference;
use App\Domain\Experience\Enums\ExperienceStatus;
use App\Domain\Experience\Models\Experience;
use App\Infrastructure\Repositories\Contracts\DiscoveryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentDiscoveryRepository implements DiscoveryRepositoryInterface
{
    public function findPreferencesByUser(int $userId): ?DiscoveryPreference
    {
        return DiscoveryPreference::where('user_id', $userId)->first();
    }

    public function createOrUpdatePreferences(int $userId, array $preferences): DiscoveryPreference
    {
        return DiscoveryPreference::updateOrCreate(
            ['user_id' => $userId],
            $preferences
        );
    }

    public function getPersonalizedSuggestions(int $userId, int $limit = 10): Collection
    {
        $preferences = $this->findPreferencesByUser($userId);
        
        $query = Experience::where('status', ExperienceStatus::APPROVED)
            ->with(['provider']);

        if ($preferences) {
            if ($preferences->preferred_types) {
                $query->whereIn('type', $preferences->preferred_types);
            }

            if ($preferences->preferred_regions) {
                $regions = is_array($preferences->preferred_regions) 
                    ? $preferences->preferred_regions 
                    : [$preferences->preferred_regions];
                $query->where(function ($q) use ($regions) {
                    foreach ($regions as $region) {
                        $q->orWhereJsonContains('location->region', $region);
                    }
                });
            }

            if ($preferences->preferred_tags) {
                $tags = is_array($preferences->preferred_tags) 
                    ? $preferences->preferred_tags 
                    : [$preferences->preferred_tags];
                $query->where(function ($q) use ($tags) {
                    foreach ($tags as $tag) {
                        $q->orWhereJsonContains('tags', $tag);
                    }
                });
            }

            if ($preferences->min_price) {
                $query->where('price', '>=', $preferences->min_price);
            }

            if ($preferences->max_price) {
                $query->where('price', '<=', $preferences->max_price);
            }

            if ($preferences->prefer_featured) {
                $query->where('is_featured', true);
            }
        }

        return $query->orderBy('is_featured', 'desc')
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTrendingExperiences(int $limit = 10): Collection
    {
        return Experience::where('status', ExperienceStatus::APPROVED)
            ->with(['provider'])
            ->orderBy('bookings_count', 'desc')
            ->orderBy('views_count', 'desc')
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getHiddenGems(int $userId, int $limit = 10): Collection
    {
        // Expériences peu connues mais bien notées
        return Experience::where('status', ExperienceStatus::APPROVED)
            ->with(['provider'])
            ->where('views_count', '<', 50) // Peu vues
            ->where(function ($query) {
                $query->where('rating', '>=', 4.0) // Bien notées
                    ->orWhereNull('rating'); // Ou pas encore notées
            })
            ->where('bookings_count', '>', 0) // Au moins une réservation
            ->orderBy('rating', 'desc')
            ->orderBy('bookings_count', 'desc')
            ->limit($limit)
            ->get();
    }
}


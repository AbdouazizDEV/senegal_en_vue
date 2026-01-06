<?php

namespace App\Application\Discovery\Handlers;

use App\Application\Discovery\Commands\UpdatePreferencesCommand;
use App\Domain\Discovery\Models\DiscoveryPreference;
use App\Infrastructure\Repositories\Contracts\DiscoveryRepositoryInterface;

class UpdatePreferencesHandler
{
    public function __construct(
        private DiscoveryRepositoryInterface $discoveryRepository
    ) {}

    public function handle(UpdatePreferencesCommand $command): DiscoveryPreference
    {
        $preferences = array_filter([
            'preferred_types' => $command->preferredTypes,
            'preferred_regions' => $command->preferredRegions,
            'preferred_tags' => $command->preferredTags,
            'min_price' => $command->minPrice,
            'max_price' => $command->maxPrice,
            'min_duration_minutes' => $command->minDurationMinutes,
            'max_duration_minutes' => $command->maxDurationMinutes,
            'preferred_participants' => $command->preferredParticipants,
            'budget_range' => $command->budgetRange,
            'interests' => $command->interests,
            'prefer_featured' => $command->preferFeatured,
            'prefer_eco_friendly' => $command->preferEcoFriendly,
            'prefer_certified_providers' => $command->preferCertifiedProviders,
        ], fn($value) => $value !== null);

        return $this->discoveryRepository->createOrUpdatePreferences(
            $command->userId,
            $preferences
        );
    }
}




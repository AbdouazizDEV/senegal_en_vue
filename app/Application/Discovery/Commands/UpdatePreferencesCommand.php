<?php

namespace App\Application\Discovery\Commands;

readonly class UpdatePreferencesCommand
{
    public function __construct(
        public int $userId,
        public ?array $preferredTypes = null,
        public ?array $preferredRegions = null,
        public ?array $preferredTags = null,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public ?int $minDurationMinutes = null,
        public ?int $maxDurationMinutes = null,
        public ?int $preferredParticipants = null,
        public ?array $budgetRange = null,
        public ?array $interests = null,
        public ?bool $preferFeatured = null,
        public ?bool $preferEcoFriendly = null,
        public ?bool $preferCertifiedProviders = null,
    ) {}
}


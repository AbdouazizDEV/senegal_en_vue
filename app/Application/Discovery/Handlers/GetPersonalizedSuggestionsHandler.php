<?php

namespace App\Application\Discovery\Handlers;

use App\Application\Discovery\Queries\GetPersonalizedSuggestionsQuery;
use App\Infrastructure\Repositories\Contracts\DiscoveryRepositoryInterface;
use App\Presentation\Http\Resources\ExperienceResource;

class GetPersonalizedSuggestionsHandler
{
    public function __construct(
        private DiscoveryRepositoryInterface $discoveryRepository
    ) {}

    public function handle(GetPersonalizedSuggestionsQuery $query): \Illuminate\Support\Collection
    {
        $experiences = $this->discoveryRepository->getPersonalizedSuggestions(
            $query->userId,
            $query->limit
        );

        return $experiences->map(fn($exp) => new ExperienceResource($exp));
    }
}




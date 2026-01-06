<?php

namespace App\Application\Discovery\Handlers;

use App\Application\Discovery\Queries\GetTrendingExperiencesQuery;
use App\Infrastructure\Repositories\Contracts\DiscoveryRepositoryInterface;
use App\Presentation\Http\Resources\ExperienceResource;

class GetTrendingExperiencesHandler
{
    public function __construct(
        private DiscoveryRepositoryInterface $discoveryRepository
    ) {}

    public function handle(GetTrendingExperiencesQuery $query): \Illuminate\Support\Collection
    {
        $experiences = $this->discoveryRepository->getTrendingExperiences($query->limit);

        return $experiences->map(fn($exp) => new ExperienceResource($exp));
    }
}




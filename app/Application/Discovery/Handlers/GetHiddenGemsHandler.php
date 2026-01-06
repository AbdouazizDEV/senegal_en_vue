<?php

namespace App\Application\Discovery\Handlers;

use App\Application\Discovery\Queries\GetHiddenGemsQuery;
use App\Infrastructure\Repositories\Contracts\DiscoveryRepositoryInterface;
use App\Presentation\Http\Resources\ExperienceResource;

class GetHiddenGemsHandler
{
    public function __construct(
        private DiscoveryRepositoryInterface $discoveryRepository
    ) {}

    public function handle(GetHiddenGemsQuery $query): \Illuminate\Support\Collection
    {
        $experiences = $this->discoveryRepository->getHiddenGems(
            $query->userId,
            $query->limit
        );

        return $experiences->map(fn($exp) => new ExperienceResource($exp));
    }
}




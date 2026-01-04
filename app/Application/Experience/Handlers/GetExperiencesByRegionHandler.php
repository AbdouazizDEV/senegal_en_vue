<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\GetExperiencesByRegionQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class GetExperiencesByRegionHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(GetExperiencesByRegionQuery $query)
    {
        return $this->experienceRepository->getByRegion($query->region, $query->perPage);
    }
}


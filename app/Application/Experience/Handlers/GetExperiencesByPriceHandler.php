<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\GetExperiencesByPriceQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class GetExperiencesByPriceHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(GetExperiencesByPriceQuery $query)
    {
        return $this->experienceRepository->getByPriceRange($query->minPrice, $query->maxPrice, $query->perPage);
    }
}


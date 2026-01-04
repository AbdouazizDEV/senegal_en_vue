<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\GetFeaturedExperiencesQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class GetFeaturedExperiencesHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(GetFeaturedExperiencesQuery $query)
    {
        return $this->experienceRepository->getFeatured($query->limit);
    }
}


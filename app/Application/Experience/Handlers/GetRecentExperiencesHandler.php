<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\GetRecentExperiencesQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class GetRecentExperiencesHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(GetRecentExperiencesQuery $query)
    {
        return $this->experienceRepository->getRecent($query->limit);
    }
}


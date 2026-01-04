<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\GetSimilarExperiencesQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class GetSimilarExperiencesHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(GetSimilarExperiencesQuery $query)
    {
        return $this->experienceRepository->getSimilar($query->experienceId, $query->limit);
    }
}


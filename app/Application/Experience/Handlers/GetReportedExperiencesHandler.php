<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\GetReportedExperiencesQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class GetReportedExperiencesHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(GetReportedExperiencesQuery $query)
    {
        return $this->experienceRepository->getReported($query->perPage);
    }
}


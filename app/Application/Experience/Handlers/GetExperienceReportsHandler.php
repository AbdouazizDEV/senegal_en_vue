<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\GetExperienceReportsQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class GetExperienceReportsHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(GetExperienceReportsQuery $query)
    {
        return $this->experienceRepository->getReports($query->perPage);
    }
}


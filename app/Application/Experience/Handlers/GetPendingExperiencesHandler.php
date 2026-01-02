<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\GetPendingExperiencesQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class GetPendingExperiencesHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(GetPendingExperiencesQuery $query)
    {
        return $this->experienceRepository->getPending($query->perPage);
    }
}


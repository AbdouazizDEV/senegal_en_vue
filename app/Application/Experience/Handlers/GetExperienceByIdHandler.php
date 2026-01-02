<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\GetExperienceByIdQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class GetExperienceByIdHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(GetExperienceByIdQuery $query)
    {
        return $this->experienceRepository->findById($query->experienceId);
    }
}


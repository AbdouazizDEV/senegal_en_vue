<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\GetExperiencesByThemeQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class GetExperiencesByThemeHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(GetExperiencesByThemeQuery $query)
    {
        return $this->experienceRepository->getByTheme($query->theme, $query->perPage);
    }
}


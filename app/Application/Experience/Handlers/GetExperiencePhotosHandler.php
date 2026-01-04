<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\GetExperiencePhotosQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class GetExperiencePhotosHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(GetExperiencePhotosQuery $query): array
    {
        return $this->experienceRepository->getPhotos($query->experienceId);
    }
}


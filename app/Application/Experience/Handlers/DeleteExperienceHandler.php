<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Commands\DeleteExperienceCommand;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class DeleteExperienceHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(DeleteExperienceCommand $command): bool
    {
        $experience = $this->experienceRepository->findById($command->experienceId);
        
        if (!$experience) {
            return false;
        }

        return $this->experienceRepository->delete($experience);
    }
}


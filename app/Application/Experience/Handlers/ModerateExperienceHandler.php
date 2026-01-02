<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Commands\ModerateExperienceCommand;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class ModerateExperienceHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(ModerateExperienceCommand $command)
    {
        $experience = $this->experienceRepository->findById($command->experienceId);
        
        if (!$experience) {
            throw new \RuntimeException('Expérience non trouvée');
        }

        return $this->experienceRepository->moderate($experience, $command->status, $command->reason);
    }
}


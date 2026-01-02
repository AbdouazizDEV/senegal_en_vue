<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Commands\UpdateExperienceCommand;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class UpdateExperienceHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(UpdateExperienceCommand $command)
    {
        $experience = $this->experienceRepository->findById($command->experienceId);
        
        if (!$experience) {
            throw new \RuntimeException('Expérience non trouvée');
        }

        return $this->experienceRepository->update($experience, $command->data);
    }
}


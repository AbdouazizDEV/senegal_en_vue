<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\CheckAvailabilityQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class CheckAvailabilityHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(CheckAvailabilityQuery $query): bool
    {
        $date = new \DateTime($query->date);
        return $this->experienceRepository->checkAvailability(
            $query->experienceId,
            $date,
            $query->participants
        );
    }
}

